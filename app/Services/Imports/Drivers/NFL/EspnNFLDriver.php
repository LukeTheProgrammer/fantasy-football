<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Facades\Action;
use App\Facades\Data;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerMissing;
use App\Models\PlayerTeam;
use App\Models\Team;

class EspnNFLDriver extends BaseNFLDriver
{
    public function importRosters(Team $team, int $season)
    {
        $roster = Data::espn()->getNFLRosters($team, $season);

        foreach ($roster as $player) {
            $playerModel = Player::espnId($player['id'])->first();

            if (!$playerModel instanceof Player) {
                Action::model(PlayerMissing::class)->upsert(
                    $player,
                    get_called_class(),
                    [
                        'unique_id_key'   => 'espn_id',
                        'unique_id_value' => $player['id'] ?? null,
                        'name'            => $player['name'] ?? null,
                        'position_id'     => $player['position'] ?? null,
                        'team_id'         => $team->id,
                    ],
                );

                continue;
            }

            Action::model(PlayerTeam::class)->upsert($playerModel, $team);
        }
    }

    public function importSchedule(Team $team, int $season)
    {
        $schedule = Data::espn()->getNFLSchedule($team, $season);

        foreach ($schedule as $game) {
            NflGame::updateOrCreate(
                ['espn_id' => $game['espn_id']],
                $game
            );
        }

        // Set up Bye week
        $weeks = NflGame::select('week')
            ->forTeam($team)
            ->forSeason($season)
            ->get()
            ->pluck('week')
            ->toArray();

        for ($week = 1; $week <= 18; $week++) {
            if (in_array($week, $weeks)) {
                continue;
            }

            NflGame::updateOrCreate([
                'week'         => $week,
                'season'       => $season,
                'home_team_id' => $team->id,
                'away_team_id' => null,
                'is_bye'       => true,
                'is_completed' => true,
            ]);

            break;
        }
    }
}
