<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;

class GetTeamNews extends NFLResource
{
    public ?Team $team = null;

    public function setCacheFilePath()
    {
        $dirs = ['news'];

        $file = [
            'team',
            $this->team->espn_id,
            date('Y-m-d'),
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/news/team-123456-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('news');

        $response = $this->get($url, $this->query([
            'team' => $this->team->espn_id,
        ]));

        return $this->returnResponse($response);
    }
}
