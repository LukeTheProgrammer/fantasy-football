<?php

namespace App\Services\CBS\Resources;

class RosterGridResource extends BaseResource
{
    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'league',
            date('Y-m-d'),
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        // $url = $this->assembleUrl([]);
        $url = 'https://crf1466606121.football.cbssports.com/teams/roster-grid';
        dump($url);

        $response = $this->get($url);
        dump($response->status());

        // return $this->returnResponse($response);
        $fp = storage_path('data/cbs/cbs-roster-grid-' . time() . '.html');
        file_put_contents($fp, $response->body());

        return $fp;
    }
}
