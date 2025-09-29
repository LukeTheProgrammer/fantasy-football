<?php

namespace Database\Seeders;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/players.json');
        $json = file_get_contents($path);
        $players = json_decode($json, true);

        foreach ($players as $player) {
            // $posId = Arr::get($player, 'position_id');
            // $player['position_id'] = Position::forAbbreviation($posId)->first()->id;
            Action::model(Player::class)->upsert($player);
        }
    }
}
