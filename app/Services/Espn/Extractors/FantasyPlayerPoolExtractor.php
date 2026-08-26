<?php

namespace App\Services\Espn\Extractors;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Pulls the player entries out of a player pool response.
 *
 * The pool arrives under a different key to the rest of the fantasy API, and
 * every entry wraps the player one level down, so this flattens to the player
 * objects themselves.
 */
class FantasyPlayerPoolExtractor
{
    public static function from(mixed $data)
    {
        if ($data instanceof Response) {
            $data = $data->json();
        }

        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        return self::fromArray((array) $data);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function fromArray(array $data): Collection
    {
        $entries = Arr::get($data, 'players', $data);

        return collect($entries)
            ->map(fn ($entry) => Arr::get($entry, 'player', $entry))
            ->filter(fn ($player) => !empty(Arr::get($player, 'id')))
            ->values();
    }
}
