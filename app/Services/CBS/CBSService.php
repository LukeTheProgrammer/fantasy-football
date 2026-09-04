<?php

namespace App\Services\CBS;

use App\Enums\Datum;
use App\Services\CBS\Data\FantasyNFL\CredentialsData;
use App\Services\CBS\Formatters\FantasyLeagueFormatter;
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
            ->dataFormat(Datum::FORMAT_RAW->value)
            ->forcePull($this->forcePull)
            ->fetch();
    }

    /**
     * CBS has no one league endpoint, so a league is the several reads put
     * together. The keeper list omits the calling user's own team, whose
     * keepers are read off that team's roster instead.
     */
    public function getFantasyLeague(array|CredentialsData $credentials, ?int $season = null)
    {
        $payloads = [
            'details'     => $this->getFantasyDetails($credentials),
            'owners'      => $this->getFantasyOwners($credentials),
            'rules'       => $this->getFantasyRules($credentials),
            'scoring'     => $this->getFantasyScoringRules($credentials),
            'draftConfig' => $this->getFantasyDraftConfig($credentials),
            'draftOrder'  => $this->getFantasyDraftOrder($credentials),
            'keepers'     => $this->getFantasyKeepers($credentials),
        ];

        $data = FantasyLeagueFormatter::from($payloads, $season ?? (int) date('Y'));

        $owned = array_column($data['keepers'], 'league_member_id');

        // Asked for no team in particular, CBS answers with the signed in
        // user's own, which is the only thing that says which team is theirs.
        $ownRoster = $this->getFantasyRoster($credentials);

        $data['ownTeamId'] = data_get($ownRoster, 'rosters.teams.0.id');

        foreach (FantasyLeagueFormatter::keepersFromRoster($ownRoster) as $keeper) {
            if (!in_array($keeper['league_member_id'], $owned, true)) {
                $data['keepers'][] = $keeper;
            }
        }

        return $data;
    }

    private function resource(string $class, array|CredentialsData $credentials)
    {
        $resource = new $class($credentials);

        return $resource->dataFormat(Datum::FORMAT_RAW->value)
            ->forcePull($this->forcePull)
            ->fetch();
    }
}
