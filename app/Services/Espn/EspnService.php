<?php

namespace App\Services\Espn;

use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Resources\BaseResource;
use App\Services\Espn\Resources\FantasyNFL;
use App\Services\Espn\Resources\NFL;
use App\Services\Espn\Resources\NflTeam;

/**
 * @see https://github.com/pseudo-r/Public-ESPN-API
 * @see https://gist.github.com/nntrn/ee26cb2a0716de0947a0a4e9a157bc1c/2fa98612cedcbad033d4206b16cd360c9b654ae9
 */
class EspnService
{
    public const RETURN_RAW       = 'raw';
    public const RETURN_FORMATTED = 'formatted';
    public const RETURN_EXTRACTED = 'extracted';

    public const RETURN_TYPES = [
        self::RETURN_RAW,
        self::RETURN_FORMATTED,
        self::RETURN_EXTRACTED,
    ];

    protected string $returnType = self::RETURN_RAW;

    protected bool $forcePull = false;

    public function raw()
    {
        $this->returnType = self::RETURN_RAW;
        return $this;
    }

    public function extracted()
    {
        $this->returnType = self::RETURN_EXTRACTED;
        return $this;
    }

    public function formatted()
    {
        $this->returnType = self::RETURN_FORMATTED;
        return $this;
    }

    public function forcePull()
    {
        $this->forcePull = true;
        return $this;
    }

    public function nfl(): NFL
    {
        return new NFL();
    }

    public function nflTeam(): NflTeam
    {
        return new NflTeam();
    }

    public function fantasyNFL(array|CredentialsData $credentials): FantasyNFL
    {
        return new FantasyNFL($credentials);
    }

    public function getFantasyLeague(array|CredentialsData $credentials)
    {
        $resource = new FantasyNFL($credentials);

        return $resource->return($this->returnType)
            ->forcePull($this->forcePull)
            ->getLeague($credentials);
    }

    public function getFantasyLeagueRoster(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->return($this->returnType)
            ->forcePull($this->forcePull)
            ->getRoster($credentials, $opts);
    }
}
