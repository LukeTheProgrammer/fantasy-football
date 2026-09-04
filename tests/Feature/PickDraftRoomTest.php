<?php

namespace Tests\Feature;

use App\Facades\Pick as PickFacade;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\LeagueSettings;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickDraftRoomTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private League $league;

    private Draft $draft;

    /** @var array<string, LeagueMember> */
    private array $members = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Season::firstOrCreate(['id' => 2026], ['is_current' => true]);

        $this->league = League::create([
            'created_by_user_id' => $this->user->id,
            'name'               => 'Pick League',
            'season_id'          => 2026,
            'platform'           => 'CBS',
            'platform_id'        => 'crf1',
            'team_count'         => 2,
            'slug'               => 'pick-league-2026',
            'credentials'        => ['leagueId' => 'crf1', 'accessToken' => 'token'],
        ]);

        LeagueSettings::create([
            'league_id'        => $this->league->id,
            'ppr'              => 'half-ppr',
            'two_qb'           => false,
            'roster_size'      => 3,
            'roster_positions' => ['QB', 'RB', 'BE'],
            'starters_count'   => 2,
            'bench_count'      => 1,
        ]);

        // Numeric string array keys become integers, so the external ids are
        // carried in the value rather than the key.
        foreach ([['4', 'Mine'], ['5', 'Theirs']] as [$externalId, $name]) {
            $this->members[$externalId] = LeagueMember::create([
                'league_id' => $this->league->id,
                // A user owns at most one team per league.
                'user_id'     => $externalId === '4' ? $this->user->id : User::factory()->create()->id,
                'external_id' => $externalId,
                'team_name'   => $name,
                'is_admin'    => $externalId === '4',
            ]);
        }

        // A traded pick: team 5 owns two of the first three slots.
        $this->draft = Draft::create([
            'league_id'   => $this->league->id,
            'draft_type'  => 'linear',
            'rounds'      => 2,
            'draft_order' => ['5', '4', '5', '4'],
        ]);
    }

    public function test_the_clock_follows_the_stored_order_rather_than_a_rotation(): void
    {
        $clock = PickFacade::onTheClock($this->draft->load(['league.members', 'picks']));

        $this->assertSame(4, $clock['total']);
        $this->assertSame('Theirs', $clock['current']['team_name']);
        $this->assertSame(1, $clock['current']['overall_pick_number']);
        $this->assertSame('Mine', $clock['upcoming'][0]['team_name']);
        // Team 5 holds a traded pick, so it is up twice before team 4's second.
        $this->assertSame('Theirs', $clock['upcoming'][1]['team_name']);
    }

    public function test_a_recorded_pick_goes_to_the_team_on_the_clock(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id])
            ->assertRedirect();

        $pick = DraftPick::where('draft_id', $this->draft->id)->sole();

        $this->assertSame($this->members['5']->id, $pick->league_member_id);
        $this->assertSame(1, $pick->overall_pick_number);
        $this->assertSame(1, $pick->round);
        $this->assertFalse((bool) $pick->is_keeper);
    }

    public function test_the_clock_advances_after_a_pick_and_returns_when_it_is_undone(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id]);

        $clock = PickFacade::onTheClock($this->draft->fresh()->load(['league.members', 'picks']));
        $this->assertSame('Mine', $clock['current']['team_name']);

        $pick = DraftPick::where('draft_id', $this->draft->id)->sole();

        $this->actingAs($this->user)
            ->delete(route('drafts.board-picks.destroy', [$this->draft->id, $pick->id]))
            ->assertRedirect();

        $clock = PickFacade::onTheClock($this->draft->fresh()->load(['league.members', 'picks']));
        $this->assertSame('Theirs', $clock['current']['team_name']);
        $this->assertSame(0, $clock['made']);
    }

    public function test_any_league_member_may_record_a_pick(): void
    {
        // The team that is not the signed in user's, and not the admin either.
        $member = $this->members['5'];
        $outsider = User::factory()->create();

        $player = Player::factory()->create();

        // A member of the league records fine, admin or not.
        $this->assertFalse((bool) $member->is_admin);

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id])
            ->assertRedirect();

        // Somebody with no team in the league does not.
        $this->actingAs($outsider)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => Player::factory()->create()->id])
            ->assertForbidden();

        $this->assertSame(1, DraftPick::where('draft_id', $this->draft->id)->count());
    }

    public function test_undoing_a_pick_from_the_middle_puts_that_slot_back_on_the_clock(): void
    {
        $first = Player::factory()->create();
        $second = Player::factory()->create();

        foreach ([$first, $second] as $player) {
            $this->actingAs($this->user)
                ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id]);
        }

        // Undo the first, leaving a hole with a later pick still standing.
        $hole = DraftPick::where('draft_id', $this->draft->id)->where('overall_pick_number', 1)->sole();

        $this->actingAs($this->user)
            ->delete(route('drafts.board-picks.destroy', [$this->draft->id, $hole->id]))
            ->assertRedirect();

        $clock = PickFacade::onTheClock($this->draft->fresh()->load(['league.members', 'picks']));

        // The hole is what is on the clock, not the end of the run: counting
        // picks would have sent the room to slot 2, which is already taken.
        $this->assertSame(1, $clock['current']['overall_pick_number']);
        $this->assertSame(1, $clock['made']);

        $round = collect($clock['rounds'][0]);
        $this->assertFalse($round->firstWhere('overall_pick_number', 1)['is_made']);
        $this->assertTrue($round->firstWhere('overall_pick_number', 2)['is_made']);
        $this->assertSame($second->full_name, $round->firstWhere('overall_pick_number', 2)['player']['full_name']);

        // Refilling the hole moves the clock past both.
        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => Player::factory()->create()->id]);

        $clock = PickFacade::onTheClock($this->draft->fresh()->load(['league.members', 'picks']));

        $this->assertSame(3, $clock['current']['overall_pick_number']);
    }

    public function test_a_player_cannot_be_drafted_twice(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id]);

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $player->id])
            ->assertSessionHasErrors('player_id');

        $this->assertSame(1, DraftPick::where('draft_id', $this->draft->id)->count());
    }

    public function test_a_keeper_is_off_the_board_and_cannot_be_drafted(): void
    {
        $keeper = Player::factory()->create();

        LeagueMemberRoster::create([
            'league_member_id' => $this->members['4']->id,
            'player_id'        => $keeper->id,
            'season'           => 2026,
            'week'             => 0,
            'lineup_slot_id'   => 0,
        ]);

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $keeper->id])
            ->assertSessionHasErrors('player_id');

        $this->assertSame(0, DraftPick::where('draft_id', $this->draft->id)->count());
    }

    public function test_a_roster_slots_keepers_and_picks_into_one_lineup(): void
    {
        $keeper = Player::factory()->create(['position_id' => 'QB']);
        $drafted = Player::factory()->create(['position_id' => 'RB']);

        LeagueMemberRoster::create([
            'league_member_id' => $this->members['5']->id,
            'player_id'        => $keeper->id,
            'season'           => 2026,
            'week'             => 0,
            'lineup_slot_id'   => 0,
        ]);

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $drafted->id]);

        $rosters = PickFacade::rosters($this->draft->fresh()->load(['league.members', 'picks.player']), 0.5, false);

        $theirs = $rosters->firstWhere('external_id', '5');

        $slots = collect($theirs['slots']);

        // Each takes the slot of his own position, and how he was come by is
        // carried on the player rather than by which list he is in.
        $qb = $slots->firstWhere('slot', 'QB');
        $rb = $slots->firstWhere('slot', 'RB');

        $this->assertSame($keeper->full_name, $qb['player']['full_name']);
        $this->assertSame('Keeper', $qb['player']['source']);
        $this->assertSame($drafted->full_name, $rb['player']['full_name']);
        $this->assertSame('R1.1', $rb['player']['source']);

        // The bench is part of the shape, and is empty here.
        $this->assertTrue($slots->firstWhere('slot', 'BE')['is_starter'] === false);
        $this->assertNull($slots->firstWhere('slot', 'BE')['player']);

        // The picks are still carried in their own running order.
        $this->assertCount(1, $theirs['picks']);
    }

    public function test_the_best_player_starts_and_the_rest_go_to_the_bench(): void
    {
        // Two running backs for one starting slot: the better ranked starts.
        $better = Player::factory()->create(['position_id' => 'RB']);
        $worse = Player::factory()->create(['position_id' => 'RB']);

        foreach ([[$better, 5], [$worse, 60]] as [$player, $rank]) {
            DraftRanking::create([
                'player_id' => $player->id,
                'season'    => 2026,
                'ranked_at' => '2026-09-01',
                'type'      => 'redraft',
                'source'    => 'FantasyPros',
                'ppr'       => 0.5,
                'superflex' => false,
                'rank'      => $rank,
            ]);
        }

        // The worse player is kept, so pick order would start him if the
        // rankings were not what decided it.
        LeagueMemberRoster::create([
            'league_member_id' => $this->members['5']->id,
            'player_id'        => $worse->id,
            'season'           => 2026,
            'week'             => 0,
            'lineup_slot_id'   => 0,
        ]);

        $this->actingAs($this->user)
            ->post(route('drafts.board-picks.store', $this->draft->id), ['player_id' => $better->id]);

        $rosters = PickFacade::rosters($this->draft->fresh()->load(['league.members', 'picks.player']), 0.5, false);

        $slots = collect($rosters->firstWhere('external_id', '5')['slots']);

        $this->assertSame($better->full_name, $slots->firstWhere('slot', 'RB')['player']['full_name']);
        $this->assertSame($worse->full_name, $slots->firstWhere('slot', 'BE')['player']['full_name']);
    }
}
