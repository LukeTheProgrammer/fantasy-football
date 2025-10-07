<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Enums\Datum;
use App\Facades\Action;
use App\Facades\Data;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerNotFound;
use App\Models\PlayerTeam;
use App\Models\Team;

class EspnNFLDriver extends BaseNFLDriver
{
    public function importRosters(Team $team, int $year)
    {
        $roster = Data::espn()->getNFLRosters($team);

        foreach ($roster as $player) {
            $playerModel = Player::espnId($player['id'])->first();

            if (! $playerModel instanceof Player) {
                Action::model(PlayerNotFound::class)->upsert([
                    'source_class' => get_called_class(),
                    'source_data' => $player,
                ]);
                continue;
            }

            Action::model(PlayerTeam::class)->upsert($playerModel, $team);
        }
    }

    public function importSchedule(Team $team, int $year)
    {
        $schedule = Data::espn()->getNFLSchedule($team, $year);

        foreach ($schedule as $game) {
            NflGame::updateOrCreate(
                ['espn_id' => $game['espn_id']],
                $game
            );
        }

        // Set up Bye week
        $weeks = NflGame::select('week')
            ->forTeam($team)
            ->forYear($year)
            ->get()
            ->pluck('week')
            ->toArray();

        for ($week = 1; $week <= 18; $week++) {
            if (in_array($week, $weeks)) {
                continue;
            }

            NflGame::updateOrCreate([
                'week' => $week,
                'year' => $year,
                'home_team_id' => $team->id,
                'away_team_id' => null,
                'is_bye' => true,
                'is_completed' => true,
            ]);

            break;
        }
    }
}
