<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;
use Exception;

class GetRoster extends NFLResource
{
    public Team|null $team = null;

    public function validate()
    {
        if (empty($this->team->espn_id)) {
            throw new Exception('Team ID is required');
        }
    }

    public function setCacheFilePath()
    {
        $dirs = ['rosters'];

        $file = [
            'team',
            $this->team->espn_id,
            date('Y-m-d'),
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/roster/team-1-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams/' . $this->team->espn_id . '/roster');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
