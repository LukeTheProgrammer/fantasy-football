<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Data\NflFantasyLeagues\ResourceMatchupData;
use App\Services\Espn\Data\NflFantasyLeagues\ResourceRosterData;
use App\Services\Espn\Data\NflFantasyLeagues\ResourceSettingsData;
use App\Services\Espn\Data\NflFantasyLeagues\ResourceStandingsData;
use App\Services\Espn\Data\NflFantasyLeagues\ResourceTeamData;

class NflFantasyLeague extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V3;
    public ?ApiYears $apiYear = ApiYears::Y_2025;
    public ?Apis $api = Apis::LM_READS;
    public ?Games $game = Games::FANTASY_FOOTBALL;
    public ?Leagues $league = Leagues::NFL;
    public ?Sports $sport = Sports::FOOTBALL;

    public array $cookies = [];

    public array $views = [
        'matchup'   => 'mMatchup',
        'roster'    => 'mRoster',
        'settings'  => 'mSettings',
        'standings' => 'mStandings',
        'teams'     => 'mTeam',
        // kona_player_info,
        // mDraftDetail,
        // mLiveScoring,
        // mNav,
        // mPendingTransactions,
        // mPositionalRatings,
        // modular,
        // player_wl
        // players_wl,
        // proTeamSchedules_wl,
    ];

    public function __construct(public int|string $leagueId)
    {
        // TODO Load from the DB or something
        $this->cookies = [
            'espn_s2' => config('services.espn.default_s2'),
            'SWID'    => config('services.espn.default_swid'),
        ];
    }

    public function buildUrl()
    {
        // https://lm-api-reads.fantasy.espn.com/apis/v3/games/ffl/seasons/2025/segments/0/leagues/691509
        return $this->assembleUrl([
            'https://' . $this->api->value,
            'apis/' . $this->apiVersion->value,
            'games/' . $this->game->value,
            'seasons/' . $this->apiYear->value,
            'segments/0/leagues/' . $this->leagueId,
        ]);
    }

    public function getMatchup()
    {
        $url = $this->buildUrl();

        $response = $this->get(
            url: $url,
            query: $this->query(['view' => $this->views['matchup']]),
            cookies: $this->cookies,
        );

        return ResourceMatchupData::from($response->json());
    }

    public function getRoster()
    {
        $url = $this->buildUrl();

        $response = $this->get(
            url: $url,
            query: $this->query(['view' => $this->views['roster']]),
            cookies: $this->cookies,
        );

        return ResourceRosterData::from($response->json());
    }

    public function getSettings()
    {
        $url = $this->buildUrl();

        $response = $this->get(
            url: $url,
            query: $this->query(['view' => $this->views['settings']]),
            cookies: $this->cookies,
        );

        return ResourceSettingsData::from($response->json());
    }

    public function getStandings()
    {
        $url = $this->buildUrl();

        $response = $this->get(
            url: $url,
            query: $this->query(['view' => $this->views['standings']]),
            cookies: $this->cookies,
        );

        return ResourceStandingsData::from($response->json());
    }

    public function getTeams()
    {
        $url = $this->buildUrl();

        $response = $this->get(
            url: $url,
            query: $this->query(['view' => $this->views['teams']]),
            cookies: $this->cookies,
        );

        return ResourceTeamData::from($response->json());
    }
}
