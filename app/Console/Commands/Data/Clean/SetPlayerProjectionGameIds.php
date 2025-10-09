<?php

namespace App\Console\Commands\Data\Clean;

use App\Models\NflGame;
use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetPlayerProjectionGameIds extends Command
{
    protected $signature = 'data:clean:set-player-projection-game-ids';

    protected $description = 'Set player projection game ids';

    public function handle(): void
    {
        NflGame::all()->each(fn ($game) => $this->processNflGame($game));
    }

    protected function processNflGame(NflGame $game): void
    {

        $playerIds = Player::whereIn('team_id', [$game->home_team_id, $game->away_team_id])->select('id');

        DB::table('player_projections')
            ->where('season', $game->season)
            ->where('week', $game->week)
            ->whereIn('player_id', $playerIds)
            ->update([
                'nfl_game_id' => $game->id,
            ]);
    }
}
