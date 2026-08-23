<?php

namespace App\Services\CBS;

use App\Models\League;
use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use App\Services\CBS\Formatters\FantasyLineupFormatter;
use App\Services\CBS\Resources\FantasyNFL;
use App\Services\CBS\Resources\OwnersResource;
use App\Traits\HasDataFormats;
use Illuminate\Support\Collection;

class CBSService
{
    use HasDataFormats;

    public function getFantasyDraft(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getDraftRecap($credentials, $opts);
    }

    public function getFantasyLeague(array|CredentialsData $credentials)
    {
        $resource = new OwnersResource($credentials);

        return $resource->fetch();
    }

    public function getFantasyMatchup(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getMatchup($credentials, $opts);
    }

    public function getFantasyRoster(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getRoster($credentials, $opts);
    }

    public function getFantasySettings(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getSettings($credentials, $opts);
    }

    public function getFantasyStandings(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getStandings($credentials, $opts);
    }

    public function getFantasyTeams(array|CredentialsData $credentials, array $opts = [])
    {
        // $resource = new FantasyNFL($credentials);

        // return $resource->dataFormat($this->dataFormat)
        //     ->forcePull($this->forcePull)
        //     ->getTeams($credentials, $opts);
    }

    public function sortFantasyLineup(League $league, array|Collection $roster)
    {
        // return FantasyLineupFormatter::from($league, $roster);
    }
}
