<?php

namespace App\Services\Nflverse\Enums;

/**
 * The nflverse releases this app reads.
 *
 * nflverse publishes each dataset as a GitHub release whose tag names the
 * dataset and whose assets are one CSV per season. The case is the tag; the
 * file name is built per dataset because the naming is not uniform.
 */
enum NflverseDataset: string
{
    case PLAYERS = 'players';
    case PLAYER_STATS = 'stats_player';
    case SCHEDULES = 'schedules';
    case SNAP_COUNTS = 'snap_counts';
    case ROSTERS = 'rosters';

    /**
     * The asset name for a season, or for the whole dataset when it ships as
     * one file.
     *
     * `$window` selects between the weekly file and the season totals, which
     * are published separately for the regular season and the postseason.
     */
    public function file(?int $season = null, string $window = 'week'): string
    {
        return match ($this) {
            self::PLAYERS      => 'players.csv',
            self::SCHEDULES    => 'games.csv',
            self::PLAYER_STATS => "stats_player_{$window}_{$season}.csv",
            self::SNAP_COUNTS  => "snap_counts_{$season}.csv",
            self::ROSTERS      => "roster_{$season}.csv",
        };
    }
}
