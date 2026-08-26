<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Enums\FantasyNFLViews;
use App\Services\Espn\Extractors\FantasyPlayerPoolExtractor;
use App\Services\Espn\Formatters\FantasyPlayerPoolFormatter;
use Illuminate\Http\Client\Response;

/**
 * The whole draftable player pool, with what ESPN's leagues pay for each man.
 *
 * ESPN publishes an average auction value and an average draft position per
 * player, drawn from its own leagues. That is a market this league actually
 * drafts in, which makes it a useful third opinion beside what this league has
 * paid and what the projections say a player is worth.
 *
 * The pool endpoint returns fifty players unless told otherwise, and the limit
 * lives in a filter header rather than the query string.
 */
class GetPlayers extends FantasyNFLResource
{
    /**
     * How many players to ask for. The draftable pool is a few hundred deep;
     * this is set well past it so the tail is never quietly cut off.
     */
    public const LIMIT = 1500;

    public int|string $season;

    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'players',
            $this->season,
            date('Y-m-d'),
        ];

        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function setOpts(int|string $season)
    {
        $this->season = $season;
    }

    public function sendRequest()
    {
        $url = $this->buildUrl([FantasyNFLViews::KONA], null, $this->season);

        $response = $this->get($url, null, $this->cookies, [
            'x-fantasy-filter' => json_encode($this->filter()),
        ]);

        return $this->returnResponse($response);
    }

    /**
     * ESPN takes its paging and sorting as a JSON header rather than a query
     * string. Sorting by percent owned puts the players anyone would draft
     * first, so a truncated response still holds the ones that matter.
     *
     * @return array<string, mixed>
     */
    private function filter(): array
    {
        return [
            'players' => [
                'limit'         => self::LIMIT,
                'sortPercOwned' => [
                    'sortAsc'      => false,
                    'sortPriority' => 1,
                ],
            ],
        ];
    }

    public function returnExtracted(array|Response $response)
    {
        return FantasyPlayerPoolExtractor::from(
            (is_array($response)) ? $response : $response->json()
        );
    }

    public function returnFormatted(array|Response $response)
    {
        return FantasyPlayerPoolFormatter::from(
            (is_array($response)) ? $response : $response->json(),
            $this->season
        );
    }
}
