<?php

namespace App\Services\Data\Sources;

use App\Enums\FantasyPlatforms;
use App\Facades\CBS;
use App\Facades\Import;
use App\Models\League;
use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use InvalidArgumentException;

class CbsSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getFantasyLeague(?League $league = null, array|CredentialsData|null $credentials = null, int|string|null $season = null)
    {
        if ($league === null && $credentials === null) {
            throw new InvalidArgumentException('League or credentials must be provided');
        }

        return CBS::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getFantasyLeague(
                ($league instanceof League) ? $league->credentials : $credentials,
                $season ? (int) $season : null
            );
    }

    /* ===[ IMPORTERS ]=== */

    public function importFantasyLeague(array $leagueData = [])
    {
        if (empty($leagueData)) {
            throw new InvalidArgumentException('League data must be provided');
        }

        $importer = Import::fantasyNFL(FantasyPlatforms::CBS->value);

        return $importer->importLeague($leagueData);
    }
}
