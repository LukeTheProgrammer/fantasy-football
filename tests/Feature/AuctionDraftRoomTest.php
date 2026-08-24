<?php

namespace Tests\Feature;

use App\Models\Draft;
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
}
