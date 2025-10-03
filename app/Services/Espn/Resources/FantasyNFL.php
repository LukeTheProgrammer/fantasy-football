<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Resources\FantasyNFL\GetLeague;
use App\Services\Espn\Resources\FantasyNFL\GetRoster;
use Illuminate\Support\Arr;

class FantasyNFL extends BaseResourceCollection
{
    public function getLeague(array|CredentialsData $credentials)
    {
        $resource = new GetLeague($credentials);

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    public function getRoster(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new GetRoster($credentials);

        $resource->setOpts(
            Arr::get($opts, 'teamId'),
            Arr::get($opts, 'week'),
            Arr::get($opts, 'year'),
        );

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }
}
