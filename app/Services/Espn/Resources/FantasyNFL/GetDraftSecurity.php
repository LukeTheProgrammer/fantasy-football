<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use Illuminate\Http\Client\Response;

/**
 * The draft session token for one team.
 *
 * The live draft socket at fantasydraft.espn.com will not accept a connection
 * without this: it is the tail of the token the JOIN url carries as its `5`
 * parameter. The endpoint answers with a bare integer, not JSON.
 */
class GetDraftSecurity extends FantasyNFLResource
{
    public int|string|null $season = null;

    public function setOpts(int|string $teamId, int|string|null $season = null)
    {
        $this->teamId = $teamId;
        $this->season = $season;
    }

    /**
     * The token is short lived and tied to a single socket session, so it is
     * never cached: a reconnect must mint a new one. fetch() below skips the
     * cache entirely, which leaves this with nothing to set.
     */
    public function setCacheFilePath() {}

    /**
     * Bypass the file cache that BaseResource::fetch() would otherwise apply.
     */
    public function fetch()
    {
        $this->validate();

        return $this->sendRequest();
    }

    public function sendRequest()
    {
        // https://lm-api-reads.fantasy.espn.com/apis/v3/games/ffl/seasons/2026/segments/0/leagues/2101089884/teams/1/draftSecurity
        $url = $this->assembleUrl([
            'https://' . $this->api->value,
            'apis/' . $this->apiVersion->value,
            'games/' . $this->game->value,
            'seasons/' . ($this->season ?? $this->apiYear->value),
            'segments/0/leagues/' . $this->leagueId,
            'teams/' . $this->teamId,
            'draftSecurity',
        ]);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnResponse($response);
    }

    /**
     * Every format is the same here — the body is one integer.
     */
    public function returnRaw(array|Response $response)
    {
        return trim(is_array($response) ? (string) reset($response) : $response->body());
    }

    public function returnExtracted(array|Response $response)
    {
        return $this->returnRaw($response);
    }

    public function returnFormatted(array|Response $response)
    {
        return $this->returnRaw($response);
    }
}
