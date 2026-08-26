<?php

namespace App\Services\Nflverse;

use App\Services\Nflverse\Resources\GamesResource;
use App\Services\Nflverse\Resources\PlayersResource;
use App\Services\Nflverse\Resources\PlayerStatsResource;
use App\Traits\HasDataFormats;
use Generator;

/**
 * The open NFL data behind nflfastR, published as one CSV per season.
 *
 * It replaced scraping Pro Football Reference, which now sits behind a
 * challenge no HTTP client answers. The files are served from GitHub with no
 * key and no rate limit, so a season arrives in one request rather than three
 * hundred, and it carries what the scrape could not: air yards, snap share,
 * field goals by distance, and the ids that tie a player to every other source.
 */
class NflverseService
{
    use HasDataFormats;

    /**
     * One season of player stat lines, weekly or totalled.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function playerStats(int $season, string $window = PlayerStatsResource::WINDOW_WEEK): Generator
    {
        return (new PlayerStatsResource)
            ->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getStats($season, $window);
    }

    /**
     * Every player nflverse knows, with the ids each other source uses.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function players(): Generator
    {
        return (new PlayersResource)
            ->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getPlayers();
    }

    /**
     * Every game, optionally narrowed to one season.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function games(?int $season = null): Generator
    {
        return (new GamesResource)
            ->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getGames($season);
    }
}
