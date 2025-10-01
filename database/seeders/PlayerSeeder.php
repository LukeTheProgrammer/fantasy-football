<?php

namespace Database\Seeders;

use App\Facades\Action;
use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/players.json');
        $json = file_get_contents($path);
        $players = json_decode($json, true);

        foreach ($players as $player) {
            Action::model(Player::class)->upsert($player);
        }
    }
}
