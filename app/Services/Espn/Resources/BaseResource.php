<?php

namespace App\Services\Espn\Resources;

use App\Enums\Datum;
use App\Enums\NFLTeams;
use App\Models\Team;
use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\EspnConstants;
use App\Traits\HasDataFormats;
use App\Traits\LoadsJsonFiles;
use App\Traits\MakesHttpRequests;
use App\Traits\UsesCacheFiles;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use InvalidArgumentException;

abstract class BaseResource
{
    use HasDataFormats;
    use LoadsJsonFiles;
    use MakesHttpRequests;
    use UsesCacheFiles;

    /**
     * League ID.
     *
     * @var int|string|null
     */
    public int|string|null $leagueId = null;

    /**
     * Team ID.
     *
     * @var int|string|null
     */
    public int|string|null $teamId = null;

    /**
     * Api version.
     *
     * @var ApiVersions|null
     */
    public ?ApiVersions $apiVersion = null;

    /**
     * Api year.
     *
     * @var ApiYears|null
     */
    public ?ApiYears $apiYear = null;

    /**
     * Api.
     *
     * @var Apis|null
     */
    public ?Apis $api = null;

    /**
     * Game.
     *
     * @var Games|null
     */
    public ?Games $game = null;

    /**
     * League.
     *
     * @var Leagues|null
     */
    public ?Leagues $league = null;

    /**
     * Sport.
     *
     * @var Sports|null
     */
    public ?Sports $sport = null;

    /**
     * Main function to send request and return data.
     *
     * @return mixed
     */
    public function fetch()
    {
        $this->validate();

        $this->setCacheFilePath();

        if (!$this->forcePull && $cache = $this->getCache()) {
            return $cache;
        }

        $data = $this->sendRequest();

        $this->setCache($data);

        return $data;
    }

    abstract public function setCacheFilePath();

    abstract public function sendRequest();

    public function setTeamId(Team|NFLTeams|int|string $team)
    {
        if ($team instanceof NFLTeams) {
            $this->teamId = Arr::get(EspnConstants::TEAM_ID_MAP, $team->value);

            return;
        }

        if ($team instanceof Team) {
            $this->teamId = Arr::get(EspnConstants::TEAM_ID_MAP, $team->id);

            return;
        }

        $teamId = NFLTeams::from($team);

        if (!$teamId instanceof NFLTeams) {
            $nflTeam = Team::forEspnId($team)->first();

            if (!$nflTeam instanceof Team) {
                throw new InvalidArgumentException('Team not found: ' . $team);
            }

            $teamId = NFLTeams::from($nflTeam->id);
        }

        $this->teamId = Arr::get(EspnConstants::TEAM_ID_MAP, $teamId->value);
    }

    public function returnResponse(array|Response $response)
    {
        if ($this->dataFormat === Datum::FORMAT_EXTRACTED->value) {
            return $this->returnExtracted($response);
        }

        if ($this->dataFormat === Datum::FORMAT_FORMATTED->value) {
            return $this->returnFormatted($response);
        }

        // RETURN_RAW default
        return $this->returnRaw($response);
    }

    public function returnRaw(array|Response $response)
    {
        return (is_array($response)) ? $response : $response->json();
    }

    /**
     * Extracted data should be an instance of Spatie/Data
     * that is very close to the raw format.
     *
     * @param array|Response $response
     *
     * @return mixed
     */
    public function returnExtracted(array|Response $response)
    {
        return (is_array($response)) ? $response : $response->json();
    }

    /**
     * Formatted data should be formatted to be most efficiently
     * used by the application.
     *
     * @param array|Response $response
     *
     * @return mixed
     */
    public function returnFormatted(array|Response $response)
    {
        return (is_array($response)) ? $response : $response->json();
    }

    public function validate() {}
}
