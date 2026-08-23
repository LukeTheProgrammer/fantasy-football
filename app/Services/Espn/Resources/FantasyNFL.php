<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Resources\FantasyNFL\GetLeague;
use App\Services\Espn\Resources\FantasyNFL\GetMatchup;
use App\Services\Espn\Resources\FantasyNFL\GetDraftRecap;
use App\Services\Espn\Resources\FantasyNFL\GetRoster;
use App\Services\Espn\Resources\FantasyNFL\GetSettings;
use App\Services\Espn\Resources\FantasyNFL\GetStandings;
use App\Services\Espn\Resources\FantasyNFL\GetTeams;
use Illuminate\Support\Arr;

class FantasyNFL extends BaseResourceCollection
{
    public function getDraftRecap(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetDraftRecap($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(Arr::get($opts, 'teamId'));

        return $resource->fetch();
    }

    public function getLeague(array|CredentialsData $credentials, int|string|null $season = null)
    {
        $resource = new GetLeague($credentials);

        $resource->season = $season;
        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        return $resource->fetch();
    }

    public function getMatchup(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetMatchup($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(Arr::get($opts, 'teamId'));

        return $resource->fetch();
    }

    public function getRoster(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetRoster($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(
            Arr::get($opts, 'teamId'),
            Arr::get($opts, 'week'),
            Arr::get($opts, 'season'),
        );

        return $resource->fetch();
    }

    public function getSettings(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetSettings($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(Arr::get($opts, 'teamId'));

        return $resource->fetch();
    }

    public function getStandings(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetStandings($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(Arr::get($opts, 'teamId'));

        return $resource->fetch();
    }

    public function getTeams(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetTeams($credentials);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->setOpts(Arr::get($opts, 'teamId'));

        return $resource->fetch();
    }
}
