<?php

namespace App\Services\Picks;

use App\Models\Draft;
use App\Models\Player;
use App\Services\Picks\Actions\BuildBoardAction;
use App\Services\Picks\Actions\BuildPlayerProfileAction;
use App\Services\Picks\Actions\OnTheClockAction;
use App\Services\Picks\Actions\SlotRostersAction;
use Illuminate\Support\Collection;

/**
 * Everything a pick draft board needs.
 *
 * A pick draft is decided by the order rather than by a price, so whose turn
 * it is is the thing the room has to be right about.
 */
class PickService
{
    /**
     * @return array<string, mixed>
     */
    public function onTheClock(Draft $draft, int $upcoming = 8): array
    {
        return (new OnTheClockAction)->run($draft, $upcoming);
    }

    public function board(Draft $draft, float $ppr, bool $superflex): Collection
    {
        return (new BuildBoardAction)->run($draft, $ppr, $superflex);
    }

    /**
     * @return array<string, mixed>
     */
    public function playerProfile(Draft $draft, Player $player, float $ppr, bool $superflex): array
    {
        return (new BuildPlayerProfileAction)->run($draft, $player, $ppr, $superflex);
    }

    public function rosters(Draft $draft, float $ppr, bool $superflex): Collection
    {
        return (new SlotRostersAction)->run($draft, $ppr, $superflex);
    }
}
