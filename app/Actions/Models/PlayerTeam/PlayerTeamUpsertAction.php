<?php

namespace App\Actions\Models\PlayerTeam;

use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Team;

class PlayerTeamUpsertAction
{
    public function run(int|string|Player $player, int|string|Team $team): PlayerTeam
    {
        $player = ($player instanceof Player) ? $player : Player::findOrFail($player);
        $team = ($team instanceof Team) ? $team : Team::findOrFail($team);

        PlayerTeam::where('player_id', $player->id)->update([
            'is_current_team' => false,
        ]);

        return PlayerTeam::updateOrCreate(
            [
                'player_id' => $player->id,
                'team_id'   => $team->id,
            ],
            [
                'is_current_team' => true,
            ]
        );
    }
}
