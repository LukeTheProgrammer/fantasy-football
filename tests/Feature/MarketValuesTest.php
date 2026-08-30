<?php

namespace Tests\Feature;

use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use App\Services\Auction\Actions\CalculateMarketValuesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketValuesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_the_price_curve_is_averaged_across_the_leagues_past_auctions(): void
    {
        $this->auction(2024, [50, 30, 10]);
        $this->auction(2025, [60, 20, 10]);

        $values = (new CalculateMarketValuesAction)->run($this->auction(2026, []));

        // The newest season counts double the one before it, so rank one is
        // (60 * 1 + 50 * 0.5) / 1.5 rather than a flat 55.
        $this->assertSame([1 => 57.0, 2 => 23.0, 3 => 10.0], $values->all());
    }

    public function test_an_older_season_counts_for_less_the_further_back_it_is(): void
    {
        $this->auction(2023, [10]);
        $this->auction(2024, [10]);
        $this->auction(2025, [100]);

        $values = (new CalculateMarketValuesAction)->run($this->auction(2026, []));

        // Weights of 1, 0.5 and 0.25: (100 + 5 + 2.5) / 1.75.
        $this->assertSame(61.0, $values->get(1));
    }

    public function test_a_rank_only_one_season_reached_is_that_seasons_price(): void
    {
        $this->auction(2024, [50, 30]);
        $this->auction(2025, [60, 20, 8]);

        $values = (new CalculateMarketValuesAction)->run($this->auction(2026, []));

        // The deep end is not dragged down by a season that never got there,
        // and it is the whole price rather than a weighted fraction of one.
        $this->assertSame(8.0, $values->get(3));
    }

    public function test_prices_are_scaled_to_this_years_budget(): void
    {
        $this->auction(2025, [50, 25], budget: 100);

        $values = (new CalculateMarketValuesAction)->run($this->auction(2026, [], budget: 200));

        // The same share of a budget twice the size is twice the price.
        $this->assertSame([1 => 100.0, 2 => 50.0], $values->all());
    }

    public function test_a_league_with_no_past_auction_has_no_curve(): void
    {
        $values = (new CalculateMarketValuesAction)->run($this->auction(2026, []));

        $this->assertTrue($values->isEmpty());
    }

    /**
     * One season of the same league, with the given prices paid.
     *
     * @param array<int, int> $prices
     */
    private function auction(int $season, array $prices, int $budget = 200): Draft
    {
        // leagues.season keys into seasons.
        Season::firstOrCreate(['id' => $season], ['is_current' => false]);

        $league = League::create([
            'created_by_user_id' => $this->user->id,
            'name'               => 'Test League',
            'season_id'          => $season,
            'platform'           => 'ESPN',
            'platform_id'        => '1',
            'team_count'         => 1,
            'slug'               => 'test-league-' . $season,
        ]);

        $member = LeagueMember::create([
            'league_id' => $league->id,
            'user_id'   => $this->user->id,
            'team_name' => 'My Team',
        ]);

        $draft = Draft::create([
            'league_id'      => $league->id,
            'draft_type'     => 'auction',
            'auction_budget' => $budget,
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

        return $draft;
    }
}
