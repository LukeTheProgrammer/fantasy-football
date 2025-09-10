<?php

namespace App\Services\Espn;

use App\Services\Espn\Resources\NFL;
use App\Services\Espn\Resources\NflTeam;
use App\Services\Espn\Resources\FantasyNFL;
use App\Services\Espn\Data\FantasyNFL\FantasyNFLCredentialsData;

/**
 * @see https://github.com/pseudo-r/Public-ESPN-API
 * @see https://gist.github.com/nntrn/ee26cb2a0716de0947a0a4e9a157bc1c/2fa98612cedcbad033d4206b16cd360c9b654ae9
 */
class EspnService
{
    public function nfl(): NFL
    {
        return new NFL();
    }

    public function nflTeam(int|string $teamId): NflTeam
    {
        return new NflTeam($teamId);
    }

    public function fantasyNFL(array|FantasyNFLCredentialsData $credentials): FantasyNFL
    {
        return new FantasyNFL($credentials);
    }
}
