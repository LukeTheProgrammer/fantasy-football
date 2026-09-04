<?php

namespace App\Services\CBS;

use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use App\Services\CBS\Resources\FantasyNFL\GetDetails;
use App\Services\CBS\Resources\FantasyNFL\GetDraftConfig;
use App\Services\CBS\Resources\FantasyNFL\GetDraftOrder;
use App\Services\CBS\Resources\FantasyNFL\GetDraftResults;
use App\Services\CBS\Resources\FantasyNFL\GetKeepers;
use App\Services\CBS\Resources\FantasyNFL\GetOwners;
use App\Services\CBS\Resources\FantasyNFL\GetRosters;
use App\Services\CBS\Resources\FantasyNFL\GetRules;
use App\Services\CBS\Resources\FantasyNFL\GetScoringRules;
use App\Traits\HasDataFormats;

class CBSService
{
    use HasDataFormats;

    public function getFantasyDetails(array|CredentialsData $credentials)
    {
        return $this->resource(GetDetails::class, $credentials);
    }

    public function getFantasyOwners(array|CredentialsData $credentials)
    {
        return $this->resource(GetOwners::class, $credentials);
    }

    public function getFantasyRules(array|CredentialsData $credentials)
    {
        return $this->resource(GetRules::class, $credentials);
    }

    public function getFantasyScoringRules(array|CredentialsData $credentials)
    {
        return $this->resource(GetScoringRules::class, $credentials);
    }

    public function getFantasyDraftConfig(array|CredentialsData $credentials)
    {
        return $this->resource(GetDraftConfig::class, $credentials);
    }

    public function getFantasyDraftOrder(array|CredentialsData $credentials)
    {
        return $this->resource(GetDraftOrder::class, $credentials);
    }

    public function getFantasyDraftResults(array|CredentialsData $credentials)
    {
        return $this->resource(GetDraftResults::class, $credentials);
    }

    public function getFantasyKeepers(array|CredentialsData $credentials)
    {
        return $this->resource(GetKeepers::class, $credentials);
    }

    /**
     * With no team id CBS answers with the calling user's own roster only,
     * which is how the commissioner-set keepers on that team are read.
     */
    public function getFantasyRoster(array|CredentialsData $credentials, int|string|null $teamId = null)
    {
        $resource = new GetRosters($credentials);

        return $resource->setOpts($teamId)
            ->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->fetch();
    }

    private function resource(string $class, array|CredentialsData $credentials)
    {
        $resource = new $class($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->fetch();
    }
}
