<?php

namespace App\Services\Auction;

use App\Models\Draft;
use App\Services\Auction\Actions\BuildCheatSheetAction;
use App\Services\Auction\Actions\CalculateMarketValuesAction;
use App\Services\Auction\Actions\CalculateProjectedValuesAction;
use App\Services\Auction\Actions\SummariseTeamsAction;
use Illuminate\Support\Collection;

/**
 * Everything the auction cheat sheet needs to price a player.
 *
 * Two independent estimates are produced: what this league has historically
 * paid for a player of that rank, and what the player is worth against the
 * budget given his projection. They disagree often, and the disagreement is
 * the useful part.
 */
class AuctionService
{
    /**
     * Every draftable player with both value estimates attached.
     */
    public function cheatSheet(Draft $draft): Collection
    {
        return (new BuildCheatSheetAction)->run($draft);
    }

    /**
     * Dollar value per overall rank, taken from the shape of what this league
     * spent the last time it drafted.
     *
     * @return Collection<int, float> Keyed by rank.
     */
    public function marketValues(Draft $draft): Collection
    {
        return (new CalculateMarketValuesAction)->run($draft);
    }

    /**
     * Dollar value per player, from projected points above replacement spread
     * across the league's total budget.
     *
     * @return Collection<int, float> Keyed by player id.
     */
    public function projectedValues(Draft $draft): Collection
    {
        return (new CalculateProjectedValuesAction)->run($draft);
    }

    /**
     * Each team's spend, remaining budget, open roster spots and max bid.
     */
    public function teams(Draft $draft): Collection
    {
        return (new SummariseTeamsAction)->run($draft);
    }
}
