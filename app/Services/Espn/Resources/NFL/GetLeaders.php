<?php

namespace App\Services\Espn\Resources\NFL;

use App\Services\Espn\Enums\ApiVersions;

class GetLeaders extends NFLResource
{
    public function setCacheFilePath()
    {
        $dirs = ['leaders'];

        $file = [
            'leaders',
            date('Y-m-d'),
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/leaders/leaders-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('leaders', ApiVersions::V3->value);

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
