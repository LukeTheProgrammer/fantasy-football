<?php

namespace App\Services\Espn\Resources\NFL;

use Exception;

class GetRoster extends NFLResource
{
    public int|string|null $teamId = null;

    public function validate()
    {
        if (empty($this->teamId)) {
            throw new Exception('Team ID is required');
        }
    }

    public function setCacheFilePath()
    {
        $dirs = ['rosters'];

        $file = [
            'team',
            $this->teamId,
            date('Y-m-d'),
            $this->returnType,
        ];

        // EX: data/espn/nfl/roster/team-1-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams/' . $this->teamId . '/roster');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
