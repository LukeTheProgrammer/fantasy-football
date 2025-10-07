<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;
use Exception;
use App\Services\Espn\Extractors\NFLScheduleExtractor;
use App\Services\Espn\Formatters\NFLScheduleFormatter;
use Illuminate\Http\Client\Response;

class GetSchedule extends NFLResource
{
    public ?Team $team = null;

    public int|string|null $year = null;

    public function validate()
    {
        if (empty($this->team)) {
            throw new Exception('Team is required');
        }

        if (empty($this->year)) {
            throw new Exception('Year is required');
        }
    }

    public function setCacheFilePath()
    {
        $dirs = ['teams'];

        $file = [
            'schedule',
            $this->team->espn_id,
            $this->year,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/teams/schedule-1-2025-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams/' . $this->team->espn_id . '/schedule');

        $response = $this->get($url, $this->query([
            'season' => $this->year,
            'seasonType' => '2',
        ]));

        return $this->returnResponse($response);
    }

    public function returnExtracted(array|Response $response)
    {
        return NFLScheduleExtractor::from(
            (is_array($response)) ? $response : $response->json()
        );
    }

    public function returnFormatted(array|Response $response)
    {
        return NFLScheduleFormatter::from(
            (is_array($response)) ? $response : $response->json()
        );
    }
}
