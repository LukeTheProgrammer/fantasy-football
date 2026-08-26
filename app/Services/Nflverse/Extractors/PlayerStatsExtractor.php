<?php

namespace App\Services\Nflverse\Extractors;

use App\Enums\NFLTeams;
use App\Enums\SeasonType;
use Illuminate\Support\Arr;

/**
 * Turns one CSV row into typed values under nflverse's own column names.
 *
 * Everything arrives as a string, and an empty string is not a zero: a receiver
 * with no target share and a receiver with a target share of nothing are
 * different facts, so blanks stay null and only counting stats are floored.
 */
class PlayerStatsExtractor
{
    /**
     * @param array<string, string> $row
     *
     * @return array<string, mixed>
     */
    public function extract(array $row, int $season, bool $weekly = true): array
    {
        $extracted = [
            'gsis_id'     => $this->string($row, 'player_id'),
            'full_name'   => $this->string($row, 'player_display_name') ?? $this->string($row, 'player_name'),
            'position'    => $this->string($row, 'position'),
            'headshot'    => $this->string($row, 'headshot_url'),
            'season'      => $season,
            'season_type' => SeasonType::fromSource($this->string($row, 'season_type')),
        ];

        if ($weekly) {
            $extracted += [
                'week'          => $this->int($row, 'week'),
                'game_id'       => $this->string($row, 'game_id'),
                'team'          => $this->team($row, 'team'),
                'opponent_team' => $this->team($row, 'opponent_team'),
            ];
        } else {
            $extracted += [
                'team'  => $this->team($row, 'recent_team'),
                'games' => $this->int($row, 'games'),
            ];
        }

        foreach (self::COUNTS as $key) {
            $extracted[$key] = $this->int($row, $key);
        }

        foreach (self::RATES as $key) {
            $extracted[$key] = $this->float($row, $key);
        }

        return $extracted;
    }

    /**
     * Counting stats, which are zero when absent.
     *
     * @var array<int, string>
     */
    public const COUNTS = [
        'completions', 'attempts', 'passing_yards', 'passing_tds', 'passing_interceptions',
        'sacks_suffered', 'passing_air_yards', 'passing_yards_after_catch', 'passing_first_downs',
        'passing_2pt_conversions',
        'carries', 'rushing_yards', 'rushing_tds', 'rushing_first_downs', 'rushing_2pt_conversions',
        'receptions', 'targets', 'receiving_yards', 'receiving_tds', 'receiving_air_yards',
        'receiving_yards_after_catch', 'receiving_first_downs', 'receiving_2pt_conversions',
        'fumbles_total', 'fumbles_lost_total', 'special_teams_tds',
        'punt_returns', 'punt_return_yards', 'kickoff_returns', 'kickoff_return_yards',
        'fg_made', 'fg_att', 'fg_blocked', 'fg_long',
        'fg_made_0_19', 'fg_made_20_29', 'fg_made_30_39', 'fg_made_40_49', 'fg_made_50_59', 'fg_made_60_',
        'pat_made', 'pat_att',
    ];

    /**
     * Rates and shares, which stay null when absent.
     *
     * @var array<int, string>
     */
    public const RATES = [
        'passing_epa', 'rushing_epa', 'receiving_epa',
        'target_share', 'air_yards_share', 'wopr',
        'fantasy_points', 'fantasy_points_ppr',
    ];

    private function string(array $row, string $key): ?string
    {
        $value = trim((string) Arr::get($row, $key, ''));

        return $value === '' || strtoupper($value) === 'NA' ? null : $value;
    }

    private function int(array $row, string $key): int
    {
        return (int) round((float) ($this->string($row, $key) ?? 0));
    }

    private function float(array $row, string $key): ?float
    {
        $value = $this->string($row, $key);

        return $value === null ? null : (float) $value;
    }

    /**
     * nflverse spells a few teams differently to the app, and a handful of
     * rows carry no team at all.
     */
    private function team(array $row, string $key): ?string
    {
        return NFLTeams::fromAbbreviation($this->string($row, $key))?->value;
    }
}
