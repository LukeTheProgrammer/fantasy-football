<?php

namespace App\Services\CBS\Resources\FantasyNFL;

use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use App\Services\CBS\Enums\Apis;
use App\Services\CBS\Enums\ApiVersions;
use App\Services\CBS\Enums\Sports;
use App\Services\CBS\Resources\BaseResource;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use RuntimeException;

abstract class FantasyNFLResource extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V3;

    public ?Apis $api = Apis::FANTASY;

    public ?Sports $sport = Sports::FOOTBALL;

    public function __construct(public array|CredentialsData $credentials)
    {
        parent::__construct();

        if (!$credentials instanceof CredentialsData) {
            $this->credentials = CredentialsData::from($credentials);
        }

        $this->leagueId = $this->credentials->leagueId;

        $this->cacheBaseDirectory = 'data/cbs/ffl';

        // Every CBS read carries the league, the format and the token.
        $this->defaultQuery = [
            'version'         => $this->apiVersion->value,
            'response_format' => 'json',
            'SPORT'           => $this->sport->value,
            'league_id'       => $this->leagueId,
            'access_token'    => $this->credentials->accessToken,
        ];
    }

    /**
     * The path under /fantasy this resource reads, e.g. 'league/draft/order'.
     */
    abstract public function path(): string;

    public function sendRequest()
    {
        $url = 'https://' . $this->api->value . '/' . $this->path();

        try {
            $response = $this->get($url, $this->query());
        } catch (RequestException $e) {
            // A dead token comes back as a 400 the http client turns into a
            // stack trace, which says nothing about what to do next.
            $this->guardResponse($e->response);

            throw $e;
        }

        $this->guardResponse($response);

        return $this->returnResponse($response);
    }

    /**
     * CBS answers an expired token with a 401 and a plain-text body, and an
     * unknown league with a 200 that says "Missing league_id". Neither is
     * JSON, so the status code alone cannot be trusted to mean success.
     */
    protected function guardResponse(Response $response): void
    {
        $body = $response->body();

        if (str_contains($body, 'Failed Authentication') || $response->status() === 401) {
            throw new RuntimeException(
                'CBS access token has expired. Open the league on cbssports.com, run CBSi.token in the '
                . 'browser console, and put the new value in CBS_ACCESS_TOKEN.'
            );
        }

        if (str_contains($body, 'User not signed in')) {
            throw new RuntimeException('CBS rejected the access token for league ' . $this->leagueId . '.');
        }

        if (str_contains($body, 'Missing league_id')) {
            throw new RuntimeException('CBS did not receive a league id.');
        }

        if ($response->json() === null) {
            throw new RuntimeException('CBS returned a non-JSON response for ' . $this->path() . '.');
        }
    }

    /**
     * CBS wraps every payload in an envelope; the caller only wants the body.
     */
    public function returnRaw(array|Response $response)
    {
        $json = is_array($response) ? $response : $response->json();

        return data_get($json, 'body', $json);
    }

    /**
     * The fantasy API authenticates on the access token in the query string,
     * so the session cookies the HTML pages need are not sent.
     */
    public function setCookies()
    {
        $this->defaultCookies = [];
    }

    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            str_replace('/', '-', $this->path()),
            date('Y-m-d'),
        ];

        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }
}
