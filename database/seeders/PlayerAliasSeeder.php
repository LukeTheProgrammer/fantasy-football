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

        if (!file_exists($path)) {
            return;
        }

        $aliases = json_decode(file_get_contents($path), true) ?? [];

        foreach ($aliases as $alias) {
            $player = $this->resolvePlayer($alias);

            if (!$player instanceof Player) {
                $this->command?->warn('No player for alias: ' . Arr::get($alias, 'name'));

                continue;
            }

            PlayerAlias::firstOrCreate([
                'player_ulid' => $player->ulid,
                'name'        => Arr::get($alias, 'name'),
            ]);
        }
    }

    /**
     * ULIDs are regenerated on a fresh player import, so the platform IDs are
     * the only stable crosswalk. ULID is the last resort.
     */
    private function resolvePlayer(array $alias): ?Player
    {
        $lookups = [
            'player_espn_id' => fn ($value) => Player::espnId($value)->first(),
            'player_pfr_id'  => fn ($value) => Player::pfrId($value)->first(),
            'player_fp_id'   => fn ($value) => Player::fpId($value)->first(),
            'player_ulid'    => fn ($value) => Player::where('ulid', '=', $value)->first(),
        ];

        foreach ($lookups as $key => $lookup) {
            $value = Arr::get($alias, $key);

            if (!$value) {
                continue;
            }

            $player = $lookup($value);

            if ($player instanceof Player) {
                return $player;
            }
        }

        return null;
    }
}
