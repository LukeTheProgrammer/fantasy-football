<?php

namespace Tests\Feature;

use App\Facades\Auction as AuctionFacade;
use App\Models\Draft;
use App\Models\DraftBudget;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionDraftRoomTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private League $league;

    private Draft $draft;

    private LeagueMember $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->league = League::create([
            'created_by_user_id' => $this->user->id,
            'name'               => 'Test League',
            'season'             => 2026,
            'platform'           => 'ESPN',
            'platform_id'        => '1',
            'team_count'         => 2,
            'slug'               => 'test-league-2026',
        ]);

        LeagueSettings::create([
            'league_id'        => $this->league->id,
            'ppr'              => 'half-ppr',
            'two_qb'           => true,
            'roster_size'      => 3,
            'roster_positions' => ['QB', 'RB', 'BE'],
        ]);

        $this->member = LeagueMember::create([
            'league_id' => $this->league->id,
            'user_id'   => $this->user->id,
            'team_name' => 'My Team',
            'is_admin'  => false,
        ]);

        $this->draft = Draft::create([
            'league_id'      => $this->league->id,
            'draft_type'     => 'auction',
            'auction_budget' => 200,
        ]);
    }

    public function test_the_draft_room_renders_the_auction_page_for_an_auction_draft(): void
    {
        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('drafts/AuctionDraftRoomPage')
                ->has('teams', 1)
                ->where('teams.0.remaining', 200)
                ->where('teams.0.max_bid', 198));
    }

    public function test_picks_are_slotted_into_the_league_roster(): void
    {
        $quarterback = Player::factory()->quarterback()->create();
        $runningBack = Player::factory()->runningBack()->create();

        // The cheaper player is recorded first to prove slotting is driven by
        // price rather than the order picks were entered.
        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $runningBack->id,
            'league_member_id' => $this->member->id,
            'amount'           => 5,
        ]);

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $quarterback->id,
            'league_member_id' => $this->member->id,
            'amount'           => 50,
        ]);

        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                ->has('rosters.' . $this->member->id, 3)
                ->where('rosters.' . $this->member->id . '.0.label', 'QB')
                ->where('rosters.' . $this->member->id . '.0.player.full_name', $quarterback->full_name)
                ->where('rosters.' . $this->member->id . '.1.label', 'RB')
                ->where('rosters.' . $this->member->id . '.1.player.full_name', $runningBack->full_name)
                ->where('rosters.' . $this->member->id . '.2.label', 'BE')
                ->where('rosters.' . $this->member->id . '.2.player', null));
    }

    public function test_a_player_with_no_slot_of_his_own_falls_to_the_bench(): void
    {
        $first = Player::factory()->quarterback()->create();
        $second = Player::factory()->quarterback()->create();

        foreach ([[$first, 40], [$second, 10]] as [$player, $amount]) {
            $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
                'player_id'        => $player->id,
                'league_member_id' => $this->member->id,
                'amount'           => $amount,
            ]);
        }

        // The roster has one QB spot, so the cheaper quarterback benches.
        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                ->where('rosters.' . $this->member->id . '.0.player.full_name', $first->full_name)
                ->where('rosters.' . $this->member->id . '.1.player', null)
                ->where('rosters.' . $this->member->id . '.2.label', 'BE')
                ->where('rosters.' . $this->member->id . '.2.player.full_name', $second->full_name));
    }

    public function test_a_sale_is_recorded_against_the_team_that_bought_the_player(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('drafts.picks.store', $this->draft), [
                'player_id'        => $player->id,
                'league_member_id' => $this->member->id,
                'amount'           => 42,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('draft_picks', [
            'draft_id'         => $this->draft->id,
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);
    }

    public function test_a_sale_reduces_the_budget_the_team_has_left(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);

        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                ->where('teams.0.spent', 42)
                ->where('teams.0.remaining', 158)
                ->where('teams.0.filled', 1)
                ->where('teams.0.open_spots', 2)
                // One dollar stays reserved for the last open spot.
                ->where('teams.0.max_bid', 157));
    }

    public function test_the_same_player_cannot_be_sold_twice(): void
    {
        $player = Player::factory()->create();

        $payload = [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 10,
        ];

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), $payload);

        $this->actingAs($this->user)
            ->post(route('drafts.picks.store', $this->draft), $payload)
            ->assertSessionHasErrors('player_id');

        $this->assertSame(1, DraftPick::where('draft_id', $this->draft->id)->count());
    }

    public function test_a_bid_cannot_exceed_the_auction_budget(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('drafts.picks.store', $this->draft), [
                'player_id'        => $player->id,
                'league_member_id' => $this->member->id,
                'amount'           => 201,
            ])
            ->assertSessionHasErrors('amount');
    }

    public function test_a_sale_can_be_corrected(): void
    {
        $player = Player::factory()->create();

        $other = LeagueMember::create([
            'league_id' => $this->league->id,
            'team_name' => 'Other Team',
            'is_admin'  => false,
        ]);

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);

        $pick = DraftPick::where('draft_id', $this->draft->id)->firstOrFail();

        $this->actingAs($this->user)
            ->patch(route('drafts.picks.update', [$this->draft, $pick]), [
                'league_member_id' => $other->id,
                'amount'           => 17,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('draft_picks', [
            'id'               => $pick->id,
            'player_id'        => $player->id,
            'league_member_id' => $other->id,
            'amount'           => 17,
        ]);
    }

    public function test_a_corrected_sale_still_cannot_exceed_the_budget(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);

        $pick = DraftPick::where('draft_id', $this->draft->id)->firstOrFail();

        $this->actingAs($this->user)
            ->patch(route('drafts.picks.update', [$this->draft, $pick]), [
                'league_member_id' => $this->member->id,
                'amount'           => 201,
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('draft_picks', ['id' => $pick->id, 'amount' => 42]);
    }

    public function test_someone_outside_the_league_cannot_correct_a_sale(): void
    {
        $outsider = User::factory()->create();
        $player = Player::factory()->create();

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);

        $pick = DraftPick::where('draft_id', $this->draft->id)->firstOrFail();

        $this->actingAs($outsider)
            ->patch(route('drafts.picks.update', [$this->draft, $pick]), [
                'league_member_id' => $this->member->id,
                'amount'           => 5,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('draft_picks', ['id' => $pick->id, 'amount' => 42]);
    }

    public function test_a_sale_can_be_undone(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 42,
        ]);

        $pick = DraftPick::where('draft_id', $this->draft->id)->firstOrFail();

        $this->actingAs($this->user)
            ->delete(route('drafts.picks.destroy', [$this->draft, $pick]))
            ->assertRedirect();

        $this->assertDatabaseMissing('draft_picks', ['id' => $pick->id]);
    }

    public function test_someone_outside_the_league_cannot_record_a_sale(): void
    {
        $outsider = User::factory()->create();
        $player = Player::factory()->create();

        $this->actingAs($outsider)
            ->post(route('drafts.picks.store', $this->draft), [
                'player_id'        => $player->id,
                'league_member_id' => $this->member->id,
                'amount'           => 42,
            ])
            ->assertForbidden();

        $this->assertSame(0, DraftPick::where('draft_id', $this->draft->id)->count());
    }

    public function test_the_market_reports_inflation_against_the_boards_own_prices(): void
    {
        $quarterback = Player::factory()->quarterback()->create();
        $runningBack = Player::factory()->runningBack()->create();

        DraftPick::create([
            'draft_id'            => $this->draft->id,
            'league_member_id'    => $this->member->id,
            'player_id'           => $quarterback->id,
            'amount'              => 60,
            'round'               => 0,
            'pick_number'         => 1,
            'overall_pick_number' => 1,
        ]);

        $sheet = collect([
            [
                'player_id'       => $quarterback->id,
                'position_id'     => 'QB',
                'rank'            => 1,
                'tier'            => 1,
                'market_value'    => 50,
                'projected_value' => 48,
                'adv'             => 45,
                'drafted_by'      => $this->member->id,
                'drafted_for'     => 60,
            ],
            [
                'player_id'       => $runningBack->id,
                'position_id'     => 'RB',
                'rank'            => 2,
                'tier'            => 2,
                'market_value'    => 30,
                'projected_value' => 28,
                'adv'             => 25,
                'drafted_by'      => null,
                'drafted_for'     => null,
            ],
        ]);

        $market = AuctionFacade::market($this->draft->fresh(['league.settings', 'league.members', 'picks.player']), $sheet);

        // $60 paid for a player the board marked at $50 is twenty percent over.
        $this->assertSame(60, $market['spent']);
        $this->assertSame(50, $market['expected']);
        $this->assertSame(20.0, $market['inflation']);
        $this->assertSame(140, $market['money_left']);
    }

    public function test_the_market_counts_what_is_left_at_each_position_and_who_still_needs_it(): void
    {
        $runningBack = Player::factory()->runningBack()->create();

        $sheet = collect([
            [
                'player_id'       => $runningBack->id,
                'position_id'     => 'RB',
                'rank'            => 1,
                'tier'            => 2,
                'market_value'    => 30,
                'projected_value' => 28,
                'adv'             => 25,
                'drafted_by'      => null,
                'drafted_for'     => null,
            ],
        ]);

        $market = AuctionFacade::market($this->draft->fresh(['league.settings', 'league.members', 'picks.player']), $sheet);

        $positions = collect($market['positions'])->keyBy('position');

        // The roster is QB, RB, BE: the bench is not a need, so one running back
        // spot is open and the whole budget is still able to chase it.
        $this->assertSame(1, $positions['RB']['available']);
        $this->assertSame(2, $positions['RB']['top_tier']);
        $this->assertSame(1, $positions['RB']['top_tier_left']);
        $this->assertSame(1, $positions['RB']['slots_open']);
        $this->assertSame(0, $positions['RB']['flex_open']);
        $this->assertSame(1, $positions['RB']['teams_needing']);
        $this->assertSame(200, $positions['RB']['money_chasing']);

        // Nothing is left at kicker, and no slot asks for one.
        $this->assertSame(0, $positions['K']['available']);
        $this->assertSame(0, $positions['K']['slots_open']);
        $this->assertSame(0, $positions['K']['flex_open']);
    }

    public function test_a_flex_slot_is_counted_beside_the_position_need_rather_than_inside_it(): void
    {
        $this->league->settings->update([
            'roster_size'      => 4,
            'roster_positions' => ['TE', 'RB_WR_TE', 'BE', 'IR'],
        ]);

        $market = AuctionFacade::market($this->draft->fresh(['league.settings', 'league.members', 'picks.player']), collect());

        $positions = collect($market['positions'])->keyBy('position');

        // The team has one tight end spot and one flex that could take another.
        // Adding those together would say it needs two tight ends, when between
        // them they are one tight end and one starter of some kind.
        $this->assertSame(1, $positions['TE']['slots_open']);
        $this->assertSame(1, $positions['TE']['flex_open']);

        // A running back has no slot of his own here, only the flex.
        $this->assertSame(0, $positions['RB']['slots_open']);
        $this->assertSame(1, $positions['RB']['flex_open']);

        // The flex still makes the team a buyer at every position it accepts.
        $this->assertSame(1, $positions['RB']['teams_needing']);
        $this->assertSame(0, $positions['QB']['teams_needing']);
    }

    public function test_the_budget_covers_every_roster_slot_bench_included(): void
    {
        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                // Every spot in the template; one of each, so no numbering.
                ->has('budget.rows', 3)
                ->where('budget.rows.0.key', 'QB')
                ->where('budget.rows.1.key', 'RB')
                ->where('budget.rows.2.key', 'BE')
                ->where('budget.budget', 200)
                ->where('budget.planned', 0)
                ->where('budget.unplanned', 200));
    }

    public function test_a_budget_can_be_saved_and_read_back(): void
    {
        $this->actingAs($this->user)
            ->put(route('drafts.budget.update', $this->draft), [
                'allocations' => ['QB' => 90, 'RB' => 60, 'BE' => 10],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('draft_budgets', [
            'draft_id'         => $this->draft->id,
            'league_member_id' => $this->member->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                ->where('budget.rows.0.planned', 90)
                ->where('budget.rows.2.planned', 10)
                ->where('budget.planned', 160)
                ->where('budget.unplanned', 40));
    }

    public function test_the_budget_reports_the_difference_against_what_was_spent(): void
    {
        $player = Player::factory()->quarterback()->create();

        $this->actingAs($this->user)->put(route('drafts.budget.update', $this->draft), [
            'allocations' => ['QB' => 40],
        ]);

        $this->actingAs($this->user)->post(route('drafts.picks.store', $this->draft), [
            'player_id'        => $player->id,
            'league_member_id' => $this->member->id,
            'amount'           => 55,
        ]);

        $this->actingAs($this->user)
            ->get(route('drafts.draft-room', $this->draft))
            ->assertInertia(fn ($page) => $page
                ->where('budget.rows.0.actual', 55)
                // Planned 40 against 55 spent: fifteen dollars over.
                ->where('budget.rows.0.difference', -15)
                ->where('budget.actual', 55)
                ->where('budget.remaining', 145));
    }

    public function test_saving_a_budget_replaces_the_previous_plan(): void
    {
        $this->actingAs($this->user)->put(route('drafts.budget.update', $this->draft), [
            'allocations' => ['QB' => 90, 'RB' => 60],
        ]);

        $this->actingAs($this->user)->put(route('drafts.budget.update', $this->draft), [
            'allocations' => ['QB' => 25, 'RB' => null],
        ]);

        $budget = DraftBudget::where('draft_id', $this->draft->id)->firstOrFail();

        $this->assertSame(['QB' => 25], $budget->allocations);
        $this->assertSame(1, DraftBudget::where('draft_id', $this->draft->id)->count());
    }

    public function test_someone_without_a_team_cannot_save_a_budget(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->put(route('drafts.budget.update', $this->draft), ['allocations' => ['QB' => 10]])
            ->assertForbidden();

        $this->assertSame(0, DraftBudget::count());
    }
}
