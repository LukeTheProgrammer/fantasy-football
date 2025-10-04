<?php

namespace App\Services\Espn\Resources\NFL;

use Exception;

class GetTeamSchedule extends NFLResource
{
    public int|string|null $teamId = null;

    public int|string|null $year = null;

    public function validate()
    {
        if (empty($this->teamId)) {
            throw new Exception('Team ID is required');
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
            $this->teamId,
            $this->year,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/teams/schedule-1-2025-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams/' . $this->teamId . '/schedule');

        $response = $this->get($url, $this->query([
            'season' => $this->year,
            'seasonType' => '2',
        ]));

        return $this->returnResponse($response);
    }
}
