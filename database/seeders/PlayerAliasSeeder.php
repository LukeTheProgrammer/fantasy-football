<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\PlayerAlias;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PlayerAliasSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/player_aliases.json');
        $json = file_get_contents($path);
        $aliases = json_decode($json, true);

        foreach ($aliases as $alias) {
            $playerId = Arr::get($alias, 'player_id');
            $player = Player::find($playerId);

            if (! $player instanceof Player) {
                dump('Player not found for alias: ' . json_encode($alias));
                continue;
            }

            PlayerAlias::updateOrCreate([ 'player_id' => $player->id, 'name' => Arr::get($alias, 'name')], []);
        }
    }
}
