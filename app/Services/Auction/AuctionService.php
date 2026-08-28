<?php

namespace App\Services\Auction;

use App\Models\Draft;
use App\Models\LeagueMember;
use App\Models\Player;
use App\Services\Auction\Actions\BuildBudgetAction;
use App\Services\Auction\Actions\BuildCheatSheetAction;
use App\Services\Auction\Actions\BuildPlayerProfileAction;
use App\Services\Auction\Actions\CalculateMarketValuesAction;
use App\Services\Auction\Actions\CalculateProjectedValuesAction;
use App\Services\Auction\Actions\SlotRostersAction;
use App\Services\Auction\Actions\SuggestBudgetsAction;
use App\Services\Auction\Actions\SummariseMarketAction;
use App\Services\Auction\Actions\SummariseTeamsAction;
use App\Services\Auction\Actions\SyncEspnPicksAction;
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
     * Pull the picks ESPN has recorded for this draft and write in the ones
     * the board does not have yet.
     *
     * @return array<string, mixed>
     */
    public function syncEspnPicks(Draft $draft): array
    {
        return (new SyncEspnPicksAction)->run($draft);
    }

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
     * One team's spending plan beside what it has actually spent.
     *
     * @return array<string, mixed>
     */
    public function budget(Draft $draft, LeagueMember $member): array
    {
        return (new BuildBudgetAction)->run($draft, $member);
    }

    /**
     * Three budgets to choose between, each built around a different position.
     *
     * @return array<int, array<string, mixed>>
     */
    public function budgetSuggestions(Draft $draft, LeagueMember $member, ?Collection $cheatSheet = null): array
    {
        return (new SuggestBudgetsAction)->run($draft, $member, $cheatSheet);
    }

    /**
     * Each team's picks placed into the roster the league is configured for,
     * keyed by league member id.
     */
    public function rosters(Draft $draft): Collection
    {
        return (new SlotRostersAction)->run($draft);
    }

    /**
     * How the room is behaving: inflation against the board's own prices, what
     * money is left, and which positions still have buyers.
     *
     * The cheat sheet is passed in when the caller already has it, so a page
     * that shows both does not build it twice.
     *
     * @return array<string, mixed>
     */
    public function market(Draft $draft, ?Collection $cheatSheet = null): array
    {
        return (new SummariseMarketAction)->run($draft, $cheatSheet ?? $this->cheatSheet($draft));
    }

    /**
     * Each team's spend, remaining budget, open roster spots and max bid.
     */
    public function teams(Draft $draft): Collection
    {
        return (new SummariseTeamsAction)->run($draft);
    }

    /**
     * One player's full history and context, for the dialog behind his name.
     *
     * @return array<string, mixed>
     */
    public function playerProfile(Draft $draft, Player $player): array
    {
        return (new BuildPlayerProfileAction)->run($draft, $player);
    }
}
