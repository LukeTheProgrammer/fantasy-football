<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;
use App\Services\Espn\Extractors\NFLScheduleExtractor;
use App\Services\Espn\Formatters\NFLScheduleFormatter;
use Exception;
use Illuminate\Http\Client\Response;

class GetSchedule extends NFLResource
{
    public ?Team $team = null;

    public int|string|null $season = null;

    public function validate()
    {
        if (empty($this->team)) {
            throw new Exception('Team is required');
        }

        if (empty($this->season)) {
            throw new Exception('Season is required');
        }
    }

    public function setCacheFilePath()
    {
        $dirs = ['teams'];

        $file = [
            'schedule',
            $this->team->espn_id,
            $this->season,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/teams/schedule-1-2025-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams/' . $this->team->espn_id . '/schedule');

        $response = $this->get($url, $this->query([
            'season' => $this->season,
            // Lowercase: ESPN ignores 'seasonType' and falls back to whatever
            // season type is currently in progress, which is preseason in August.
            'seasontype' => '2',
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
