<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Facades\Action;
use App\Facades\Data;
use App\Enums\Datum;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class EspnNFLDriver extends BaseNFLDriver
{
    public function importRosters(Team $team, int $year)
    {
        $roster = Data::espn()
            ->dataFormat(Datum::FORMAT_FORMATTED)
            ->getNFLRosters($team);

        foreach ($roster as $player) {
            $playerModel = Player::espnId($player['id'])->first();

            if (! $playerModel instanceof Player) {
                Log::error('Player not found for ESPN Roster Import', $player);
            }

            Action::model(PlayerTeam::class)->upsert($playerModel, $team);
        }
    }
}
