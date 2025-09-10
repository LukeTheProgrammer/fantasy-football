<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Data\FantasyNFL\CredentialsData;
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
use Illuminate\Support\Arr;

class FantasyNFL extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V3;
    public ?ApiYears $apiYear = ApiYears::Y_2025;
    public ?Apis $api = Apis::LM_READS;
    public ?Games $game = Games::FANTASY_FOOTBALL;
    public ?Leagues $league = Leagues::NFL;
    public ?Sports $sport = Sports::FOOTBALL;

    public array $cookies = [];

    public bool $returnRaw = false;

    public array $views = [
        'draft'               => 'mDraftDetail',
        'kona'                => 'kona_player_info',
        'liveScore'           => 'mLiveScoring',
        'matchup'             => 'mMatchup',
        'matchupScore'        => 'mMatchupScore',
        'modular'             => 'modular',
        'nav'                 => 'mNav',
        'pendingTransactions' => 'mPendingTransactions',
        'playerWL'            => 'player_wl',
        'playersWL'           => 'players_wl',
        'positionalRatings'   => 'mPositionalRatings',
        'proTeamSchedulesWL'  => 'proTeamSchedules_wl',
        'roster'              => 'mRoster',
        'settings'            => 'mSettings',
        'standings'           => 'mStandings',
        'status'              => 'mStatus',
        'teams'               => 'mTeam',
    ];

    public int $leagueId;

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
    }

    public function getData(array $views = [])
    {
        $url = $this->buildUrl($views);

        $response = $this->get($url, null, $this->cookies);

        return $response->json();
    }

    public function getLeague()
    {
        $url = $this->buildUrl();

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceLeagueData::from($response->json());
    }

    public function getMatchup()
    {
        $url = $this->buildUrl(['matchup', 'matchupScore', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceMatchupData::from($response->json());
    }

    public function getRoster()
    {
        $url = $this->buildUrl(['roster', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceRosterData::from($response->json());
    }

    public function getSettings()
    {
        $url = $this->buildUrl(['settings', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceSettingsData::from($response->json());
    }

    public function getStandings()
    {
        $url = $this->buildUrl(['standings', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceStandingsData::from($response->json());
    }

    public function getTeams()
    {
        $url = $this->buildUrl(['teams', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnRaw
            ? $response->json()
            : ResourceTeamsData::from($response->json());
    }

    public function getDraftRecap()
    {
        // ?view=mDraftDetail&view=mSettings&view=mTeam&view=modular&view=mNav
        $url = $this->buildUrl(['draft', 'settings', 'team', 'modular', 'nav']);

        $response = $this->get($url, null, $this->cookies);

        return $response->json();
    }

    private function buildUrl(array $views = []): string
    {
        // https://lm-api-reads.fantasy.espn.com/apis/v3/games/ffl/seasons/2025/segments/0/leagues/691509
        $url = $this->assembleUrl([
            'https://' . $this->api->value,
            'apis/' . $this->apiVersion->value,
            'games/' . $this->game->value,
            'seasons/' . $this->apiYear->value,
            'segments/0/leagues/' . $this->leagueId,
        ]);

        $query = $this->buildViewsQuery($views);

        return $url . $query;
    }

    private function buildViewsQuery(array $views = [])
    {
        $views = (empty($views)) ? array_keys($this->views) : $views;

        $mapped = array_map(function ($view) {
            $key = Arr::get($this->views, $view);
            return ($key) ? 'view=' . $key : null;
        }, $views);

        return '?' . implode('&', array_filter($mapped));
    }
}
