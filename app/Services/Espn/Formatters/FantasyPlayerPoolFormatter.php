<?php

namespace App\Services\Espn\Formatters;

use App\Enums\Datum;
use App\Services\Espn\Extractors\FantasyPlayerPoolExtractor;
use App\Services\Espn\Helpers\FantasyPlayerId;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Shapes ESPN's player pool into one market value row per player.
 *
 * Only the ownership numbers are kept. ESPN's own projections and ratings are
 * not read here: the app has projections from a source it already trusts, and
 * the reason to come to ESPN at all is the auction value, which nobody else
 * publishes for the leagues this one is drafted alongside.
 */
class FantasyPlayerPoolFormatter
{
    public static function from(mixed $data, int|string $season): Collection
    {
        return FantasyPlayerPoolExtractor::from($data)
            ->map(fn (array $player) => self::player($player, (int) $season))
            ->filter()
            ->values();
    }

    /**
     * @param array<string, mixed> $player
     *
     * @return array<string, mixed>|null
     */
    private static function player(array $player, int $season): ?array
    {
        $ownership = Arr::get($player, 'ownership') ?? [];

        // A player nobody owns and nobody pays for tells us nothing, and the
        // pool is mostly those.
        $adv = (float) Arr::get($ownership, 'auctionValueAverage', 0);
        $adp = (float) Arr::get($ownership, 'averageDraftPosition', 0);

        if ($adv <= 0 && $adp <= 0) {
            return null;
        }

        // A team defense is not an athlete to ESPN, so its id needs undoing
        // before anything can look the player up.
        $lookup = (new FantasyPlayerId)->lookup(
            Arr::get($player, 'id'),
            Arr::get($player, 'fullName')
        );

        return $lookup + [
            'season'          => $season,
            'source'          => Datum::SOURCE_ESPN->value,
            'adv'             => round($adv, 2),
            'adp'             => round($adp, 2),
            'percent_owned'   => round((float) Arr::get($ownership, 'percentOwned', 0), 2),
            'percent_started' => round((float) Arr::get($ownership, 'percentStarted', 0), 2),
        ];
    }
}
