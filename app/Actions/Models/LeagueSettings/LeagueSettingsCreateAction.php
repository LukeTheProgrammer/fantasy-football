<?php

namespace App\Actions\Models\LeagueSettings;

use App\Models\League;
use App\Models\LeagueSettings;
use Illuminate\Support\Arr;

class LeagueSettingsCreateAction
{
    public function run(League $league, array $data): LeagueSettings
    {
        $settings = LeagueSettings::create([
            'league_id'                   => $league->id,
            'ppr'                         => Arr::get($data, 'ppr', 'standard'),
            'two_qb'                      => Arr::get($data, 'two_qb', false),
            'roster_positions'            => Arr::get($data, 'roster_positions', []),
            'roster_size'                 => Arr::get($data, 'roster_size', 16),
            'starters_count'              => Arr::get($data, 'starters_count', 9),
            'bench_count'                 => Arr::get($data, 'bench_count', 7),
            'ir_spots'                    => Arr::get($data, 'ir_spots', 1),
            'passing_points_per_yard'     => Arr::get($data, 'passing_points_per_yard', 0.04),
            'passing_td_points'           => Arr::get($data, 'passing_td_points', 4.0),
            'interception_points'         => Arr::get($data, 'interception_points', -2.0),
            'rushing_points_per_yard'     => Arr::get($data, 'rushing_points_per_yard', 0.1),
            'rushing_td_points'           => Arr::get($data, 'rushing_td_points', 6.0),
            'receiving_points_per_yard'   => Arr::get($data, 'receiving_points_per_yard', 0.1),
            'receiving_td_points'         => Arr::get($data, 'receiving_td_points', 6.0),
            'reception_points'            => Arr::get($data, 'reception_points', 0.0),
            'fumble_lost_points'          => Arr::get($data, 'fumble_lost_points', -2.0),
            'two_point_conversion_points' => Arr::get($data, 'two_point_conversion_points', 2.0),
        ]);

        return $settings;
    }
}
