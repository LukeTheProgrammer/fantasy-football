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
        // $path = database_path('data/player_aliases.json');
        // $json = file_get_contents($path);
        // $aliases = json_decode($json, true);

        // foreach ($aliases as $alias) {
        //     $player = null;

        //     $espnId = Arr::get($alias, 'player_espn_id');

        //     if ($espnId) {
        //         $player = Player::espnId($espnId)->first();
        //     }

        //     if (! $player instanceof Player) {
        //         $pfrId = Arr::get($alias, 'player_pfr_id');

        //         if ($pfrId) {
        //             $player = Player::pfrId($pfrId)->first();
        //         }
        //     }

        //     if (! $player instanceof Player) {
        //         $fpId = Arr::get($alias, 'player_fp_id');

        //         if ($fpId) {
        //             $player = Player::fpId($fpId)->first();
        //         }
        //     }

        //     if (! $player instanceof Player) {
        //         $ulid = Arr::get($alias, 'player_ulid');

        //         if ($ulid) {
        //             $player = Player::where('ulid', '=', $ulid)->first();
        //         }
        //     }

        //     if (! $player instanceof Player) {
        //         dump('Player not found for alias: ' . json_encode($alias));
        //         continue;
        //     }

        //     PlayerAlias::updateOrCreate(
        //         [
        //             'player_ulid' => $player->ulid,
        //             'name' => Arr::get($alias, 'name'),
        //         ],
        //         [
        //             'last_checked_at' => Arr::get($alias, 'last_checked_at'),
        //         ],
        //     );
        // }
    }
}
