<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Facades\Action;
use App\Facades\Data;
use App\Models\Player;
use App\Models\PlayerMissing;
use App\Models\PlayerTeam;
use App\Models\Team;

class ProFootballReferenceDriver extends BaseNFLDriver
{
    public function importRosters(Team $team, int $season)
    {
        $roster = Data::pfr()->getNFLRosters($team, $season);

        foreach ($roster as $player) {
            $playerModel = Player::pfrId($player['pfr_id'])->first();

            if (!$playerModel instanceof Player) {
                $q = Player::where('full_name', '=', $player['full_name']);

                if ($q->count() === 1) {
                    $playerModel = $q->first();
                }
            }

            if (!$playerModel instanceof Player) {
                Action::model(PlayerMissing::class)->upsert(
                    $player,
                    get_called_class(),
                    [
                        'unique_id_key'   => 'pfr_id',
                        'unique_id_value' => $player['pfr_id'] ?? null,
                        'name'            => $player['full_name'] ?? null,
                        'position_id'     => $player['position'] ?? null,
                        'team_id'         => $team->id,
                    ],
                );

                continue;
            }

            Action::model(PlayerTeam::class)->upsert($playerModel, $team);
        }
    }
}
