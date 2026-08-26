<?php

namespace App\Services\Nflverse\Formatters;

use App\Enums\Datum;
use Illuminate\Support\Arr;

/**
 * Maps extracted nflverse values onto the app's stat columns.
 *
 * The player is not resolved here — the importer does that, because it is the
 * one place that knows what to do when a player cannot be found.
 */
class PlayerStatsFormatter
{
    /**
     * nflverse column => app column, for everything that is a straight rename.
     *
     * @var array<string, string>
     */
    public const COLUMNS = [
        'attempts'                  => 'passing_attempts',
        'completions'               => 'passing_completions',
        'passing_yards'             => 'passing_yards',
        'passing_tds'               => 'passing_touchdowns',
        'passing_interceptions'     => 'passing_interceptions',
        'sacks_suffered'            => 'passing_sacks',
        'passing_air_yards'         => 'passing_air_yards',
        'passing_yards_after_catch' => 'passing_yards_after_catch',
        'passing_first_downs'       => 'passing_first_downs',
        'passing_2pt_conversions'   => 'passing_two_point_conversions',
        'passing_epa'               => 'passing_epa',

        'carries'                 => 'rushing_attempts',
        'rushing_yards'           => 'rushing_yards',
        'rushing_tds'             => 'rushing_touchdowns',
        'rushing_first_downs'     => 'rushing_first_downs',
        'rushing_2pt_conversions' => 'rushing_two_point_conversions',
        'rushing_epa'             => 'rushing_epa',

        'targets'                     => 'receiving_targets',
        'receptions'                  => 'receiving_receptions',
        'receiving_yards'             => 'receiving_yards',
        'receiving_tds'               => 'receiving_touchdowns',
        'receiving_air_yards'         => 'receiving_air_yards',
        'receiving_yards_after_catch' => 'receiving_yards_after_catch',
        'receiving_first_downs'       => 'receiving_first_downs',
        'receiving_2pt_conversions'   => 'receiving_two_point_conversions',
        'receiving_epa'               => 'receiving_epa',

        'target_share'    => 'target_share',
        'air_yards_share' => 'air_yards_share',
        'wopr'            => 'wopr',

        'fumbles_total'        => 'fumbles',
        'fumbles_lost_total'   => 'fumbles_lost',
        'special_teams_tds'    => 'special_teams_touchdowns',
        'punt_returns'         => 'punt_returns',
        'punt_return_yards'    => 'punt_return_yards',
        'kickoff_returns'      => 'kickoff_returns',
        'kickoff_return_yards' => 'kickoff_return_yards',

        'fg_made'       => 'field_goals_made',
        'fg_att'        => 'field_goals_attempted',
        'fg_blocked'    => 'field_goals_blocked',
        'fg_long'       => 'field_goals_longest',
        'fg_made_0_19'  => 'field_goals_made_0_19',
        'fg_made_20_29' => 'field_goals_made_20_29',
        'fg_made_30_39' => 'field_goals_made_30_39',
        'fg_made_40_49' => 'field_goals_made_40_49',
        'fg_made_50_59' => 'field_goals_made_50_59',
        'fg_made_60_'   => 'field_goals_made_60_plus',
        'pat_made'      => 'extra_points_made',
        'pat_att'       => 'extra_points_attempted',

        'fantasy_points'     => 'fantasy_points',
        'fantasy_points_ppr' => 'fantasy_points_ppr',
    ];

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function format(array $data, bool $weekly = true): array
    {
        $formatted = [
            // Carried so the importer can resolve the player and the game; both
            // are dropped before the row is written.
            'gsis_id'   => Arr::get($data, 'gsis_id'),
            'full_name' => Arr::get($data, 'full_name'),
            'headshot'  => Arr::get($data, 'headshot'),

            'season'      => Arr::get($data, 'season'),
            'season_type' => Arr::get($data, 'season_type'),
            'source'      => Datum::SOURCE_NFLVERSE->value,
            'team_id'     => Arr::get($data, 'team'),
            'position_id' => Arr::get($data, 'position'),
        ];

        if ($weekly) {
            $formatted += [
                'week'             => Arr::get($data, 'week'),
                'nflverse_game_id' => Arr::get($data, 'game_id'),
                'opponent_team_id' => Arr::get($data, 'opponent_team'),
            ];
        } else {
            $formatted['games_played'] = Arr::get($data, 'games');
        }

        foreach (self::COLUMNS as $from => $to) {
            $formatted[$to] = Arr::get($data, $from);
        }

        return $formatted;
    }
}
