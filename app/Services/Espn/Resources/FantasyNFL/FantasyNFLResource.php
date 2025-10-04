<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\FantasyNFLViews;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Resources\BaseResource;

abstract class FantasyNFLResource extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V3;

    public ?ApiYears $apiYear = ApiYears::Y_2025;

    public ?Apis $api = Apis::LM_READS;

    public ?Games $game = Games::FANTASY_FOOTBALL;

    public ?Leagues $league = Leagues::NFL;

    public ?Sports $sport = Sports::FOOTBALL;

    protected array $cookies = [];

    public function __construct(public array|CredentialsData $credentials)
    {
        if (! $credentials instanceof CredentialsData) {
            $this->credentials = CredentialsData::from($credentials);
        }

        $this->leagueId = $this->credentials->leagueId;

        $this->cookies = [
            'espn_s2' => $this->credentials->s2,
            'SWID'    => $this->credentials->swid,
        ];

        $this->cacheBaseDirectory = 'data/espn/ffl';

        $this->cookieDomain = 'espn.com';
    }

    protected function buildUrl(array $views = [], ?int $teamId = null, ?int $year = null): string
    {
        $season = $year ?? $this->apiYear->value;

        // https://lm-api-reads.fantasy.espn.com/apis/v3/games/ffl/seasons/2025/segments/0/leagues/691509
        $url = $this->assembleUrl([
            'https://' . $this->api->value,
            'apis/' . $this->apiVersion->value,
            'games/' . $this->game->value,
            'seasons/' . $season,
            'segments/0/leagues/' . $this->leagueId,
        ]);

        $query = $this->buildViewsQuery($views);

        if ($teamId) {
            $query .= '&rosterForTeamId=' . $teamId;
        }

        return $url . $query;
    }

    protected function buildViewsQuery(array $views = [])
    {
        $views = empty($views) ? FantasyNFLViews::cases() : $views;

        $mapped = array_map(function ($view) {
            $viewName = ($view instanceof FantasyNFLViews)
                ? $view
                : FantasyNFLViews::tryFrom($view);

            return ($viewName) ? 'view=' . $viewName->value : null;
        }, $views);

        return '?' . implode('&', array_filter($mapped));
    }
}
