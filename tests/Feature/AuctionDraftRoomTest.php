<?php

namespace Tests\Feature;

use App\Facades\Auction as AuctionFacade;
use App\Models\Draft;
use App\Models\DraftBudget;
use App\Models\DraftPick;
use App\Models\DraftRanking;
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

    public function test_each_suggested_budget_buys_the_best_player_at_its_position(): void
    {
        $this->rankedPlayers();
        $this->priorAuction([150, 120, 60, 10, 5, 5]);

        $suggestions = collect(AuctionFacade::budgetSuggestions($this->draft, $this->member));

        $this->assertSame(['QB', 'RB', 'WR'], $suggestions->pluck('focus')->all());

        // The quarterback plan buys the best quarterback outright; the running
        // back plan spends that money on the best running back instead.
        $this->assertSame('Best Quarterback', $suggestions->firstWhere('focus', 'QB')['players']['QB']['full_name']);
        $this->assertSame('Best Runningback', $suggestions->firstWhere('focus', 'RB')['players']['RB']['full_name']);

        // The same quarterback slot is worth far more to the plan built around
        // him than to the one that spent its money at running back.
        $this->assertGreaterThan(
            $suggestions->firstWhere('focus', 'RB')['allocations']['QB'],
            $suggestions->firstWhere('focus', 'QB')['allocations']['QB'],
        );
    }

    public function test_a_suggested_budget_never_plans_past_the_budget(): void
    {
        $this->rankedPlayers();
        $this->priorAuction([150, 120, 60, 10, 5, 5]);

        foreach (AuctionFacade::budgetSuggestions($this->draft, $this->member) as $plan) {
            $this->assertLessThanOrEqual(200, $plan['planned']);
            $this->assertSame(200 - $plan['planned'], $plan['unplanned']);

            // Every slot is planned for, since a slot left at zero reads as one
            // the plan forgot rather than one it means to fill cheaply.
            $this->assertCount(4, $plan['allocations']);
        }
    }

    public function test_a_suggested_budget_keys_its_allocations_the_way_the_plan_does(): void
    {
        $this->rankedPlayers();
        $this->priorAuction([150, 120, 60, 10, 5, 5]);

        $budget = AuctionFacade::budget($this->draft, $this->member);
        $plan = AuctionFacade::budgetSuggestions($this->draft, $this->member)[0];

        // A suggestion is applied by dropping it straight into the plan, so the
        // two have to agree on what a slot is called.
        $this->assertSame(
            collect($budget['rows'])->pluck('key')->sort()->values()->all(),
            collect(array_keys($plan['allocations']))->sort()->values()->all(),
        );
    }

    public function test_every_named_starting_slot_is_given_a_player(): void
    {
        $this->rankedPlayers();
        $this->priorAuction([150, 120, 60, 10, 5, 5]);

        // A kicker and a defence are streamed for a dollar, so the plan leaves
        // them unnamed on purpose.
        $this->league->settings->update(['roster_positions' => ['QB', 'RB', 'WR', 'K', 'DST', 'BE']]);

        foreach (AuctionFacade::budgetSuggestions($this->draft, $this->member) as $plan) {
            foreach (['QB', 'RB', 'WR'] as $slot) {
                $this->assertNotNull($plan['players'][$slot], $plan['focus'] . ' plan left ' . $slot . ' empty');
            }

            $this->assertNull($plan['players']['K']);
            $this->assertNull($plan['players']['DST']);
        }
    }

    public function test_the_suggested_budgets_page_is_only_for_an_unfinished_auction(): void
    {
        $this->actingAs($this->user)
            ->get(route('drafts.budgets', $this->draft))
            ->assertInertia(fn ($page) => $page->component('drafts/SuggestedBudgetsPage'));

        $this->draft->update(['is_completed' => true]);

        $this->actingAs($this->user)
            ->get(route('drafts.budgets', $this->draft))
            ->assertRedirect(route('drafts.show', [$this->league->id, $this->league->season]));
    }

    public function test_someone_without_a_team_cannot_see_the_suggested_budgets(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('drafts.budgets', $this->draft))
            ->assertForbidden();
    }

    public function test_there_is_nothing_to_suggest_without_a_ranked_board(): void
    {
        $this->assertSame([], AuctionFacade::budgetSuggestions($this->draft, $this->member));
    }

    /**
     * A season the league has already drafted, which is where the price curve
     * a suggestion spends comes from.
     */
    private function priorAuction(array $prices): void
    {
        $league = League::create([
            'created_by_user_id' => $this->user->id,
            'name'               => 'Test League',
            'season'             => 2025,
            'platform'           => 'ESPN',
            'platform_id'        => '1',
            'team_count'         => 2,
            'slug'               => 'test-league-2025',
        ]);

        $member = LeagueMember::create([
            'league_id' => $league->id,
            'user_id'   => $this->user->id,
            'team_name' => 'My Team',
        ]);

        $draft = Draft::create([
            'league_id'      => $league->id,
            'draft_type'     => 'auction',
            'auction_budget' => 200,
            'is_completed'   => true,
        ]);

        foreach ($prices as $index => $price) {
            DraftPick::create([
                'draft_id'         => $draft->id,
                'league_member_id' => $member->id,
                'player_id'        => Player::factory()->create()->id,
                'round'            => 1,
                'pick_number'      => $index + 1,
                'amount'           => $price,
            ]);
        }
    }

    /**
     * A board with one obvious best player at each position.
     */
    private function rankedPlayers(): void
    {
        // A wide receiver slot as well, so the three plans have somewhere
        // different to put their money.
        $this->league->settings->update(['roster_positions' => ['QB', 'RB', 'WR', 'BE']]);

        $players = [
            ['Best', 'Quarterback', 'QB', 1],
            ['Second', 'Quarterback', 'QB', 4],
            ['Best', 'Runningback', 'RB', 2],
            ['Second', 'Runningback', 'RB', 5],
            ['Best', 'Receiver', 'WR', 3],
            ['Second', 'Receiver', 'WR', 6],
        ];

        foreach ($players as [$first, $last, $position, $rank]) {
            $player = Player::factory()->create([
                'first_name'  => $first,
                'last_name'   => $last,
                'full_name'   => $first . ' ' . $last,
                'position_id' => $position,
            ]);

            DraftRanking::create([
                'player_id' => $player->id,
                'season'    => 2026,
                'ranked_at' => now()->toDateString(),
                'type'      => 'redraft',
                'source'    => 'FantasyPros',
                'ppr'       => 0.5,
                'superflex' => true,
                'rank'      => $rank,
                'tier'      => 1,
            ]);
        }
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
