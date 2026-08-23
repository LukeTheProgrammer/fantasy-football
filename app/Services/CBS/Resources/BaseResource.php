<?php

namespace App\Services\CBS\Resources;

use App\Enums\Datum;
use App\Models\Team;
use App\Services\CBS\Enums\Apis;
use App\Services\CBS\Enums\ApiVersions;
use App\Services\CBS\Enums\ApiYears;
use App\Services\CBS\Enums\Games;
use App\Services\CBS\Enums\Leagues;
use App\Services\CBS\Enums\Sports;
use App\Traits\HasDataFormats;
use App\Traits\LoadsJsonFiles;
use App\Traits\MakesHttpRequests;
use App\Traits\UsesCacheFiles;
use Illuminate\Http\Client\Response;

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
