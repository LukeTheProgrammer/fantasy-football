<?php

namespace App\Actions\Models\LeagueSettings;

use App\Models\LeagueSettings;
use Illuminate\Support\Arr;

class LeagueSettingsUpdateAction
{
    public function run(LeagueSettings $settings, array $data): LeagueSettings
    {
        $settings->update([
            'roster_positions'            => Arr::get($data, 'roster_positions', $settings->roster_positions),
            'roster_size'                 => Arr::get($data, 'roster_size', $settings->roster_size),
            'starters_count'              => Arr::get($data, 'starters_count', $settings->starters_count),
            'bench_count'                 => Arr::get($data, 'bench_count', $settings->bench_count),
            'ir_spots'                    => Arr::get($data, 'ir_spots', $settings->ir_spots),
            'passing_points_per_yard'     => Arr::get($data, 'passing_points_per_yard', $settings->passing_points_per_yard),
            'passing_td_points'           => Arr::get($data, 'passing_td_points', $settings->passing_td_points),
            'interception_points'         => Arr::get($data, 'interception_points', $settings->interception_points),
            'rushing_points_per_yard'     => Arr::get($data, 'rushing_points_per_yard', $settings->rushing_points_per_yard),
            'rushing_td_points'           => Arr::get($data, 'rushing_td_points', $settings->rushing_td_points),
            'receiving_points_per_yard'   => Arr::get($data, 'receiving_points_per_yard', $settings->receiving_points_per_yard),
            'receiving_td_points'         => Arr::get($data, 'receiving_td_points', $settings->receiving_td_points),
            'reception_points'            => Arr::get($data, 'reception_points', $settings->reception_points),
            'fumble_lost_points'          => Arr::get($data, 'fumble_lost_points', $settings->fumble_lost_points),
            'two_point_conversion_points' => Arr::get($data, 'two_point_conversion_points', $settings->two_point_conversion_points),
        ]);

        return $settings->refresh();
    }
}
