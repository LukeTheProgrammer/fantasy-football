<?php

namespace App\Services\Espn\Resources\NFL;

class GetTeamNews extends NFLResource
{
    public int|string|null $teamId = null;

    public function setCacheFilePath()
    {
        $dirs = ['news'];

        $file = [
            'team',
            $this->teamId,
            date('Y-m-d'),
            $this->returnType,
        ];

        // EX: data/espn/nfl/news/team-123456-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('news');

        $response = $this->get($url, $this->query([
            'team' => $this->teamId,
        ]));

        return $this->returnResponse($response);
    }
}
