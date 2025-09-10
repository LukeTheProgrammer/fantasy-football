<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Data\FantasyNFL\FantasyNFLCredentialsData;
use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\ResourceMatchupData;
use App\Services\Espn\Data\FantasyNFL\ResourceRosterData;
use App\Services\Espn\Data\FantasyNFL\ResourceSettingsData;
use App\Services\Espn\Data\FantasyNFL\ResourceStandingsData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;

class FantasyNFL extends BaseResource
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

    public int $leagueId;

    public function __construct(public array|FantasyNFLCredentialsData $credentials)
    {
        if (! $credentials instanceof FantasyNFLCredentialsData) {
            $this->credentials = FantasyNFLCredentialsData::from($credentials);
        }

        $this->leagueId = $this->credentials->leagueId;

        $this->cookies = [
            'espn_s2' => $this->credentials->s2,
            'SWID'    => $this->credentials->swid,
        ];
    }

    public function getLeague()
    {
        $url = $this->buildUrl() . '?' . implode('&', [
            'view=mLiveScoring',
            'view=mMatchupScore',
            'view=mRoster',
            'view=mSettings',
            'view=mStandings',
            'view=mStatus',
            'view=mTeam',
            'view=modular',
            'view=mNav',
        ]);

        $response = $this->get(
            url: $url,
            query: null,
            cookies: $this->cookies,
        );

        return ResourceLeagueData::from($response->json());
        // return $response->json();
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

        return ResourceTeamsData::from($response->json());
    }

    private function buildUrl()
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
}
