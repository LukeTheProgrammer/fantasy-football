<?php

namespace App\Services\Espn\Resources\NFL;

class GetEventSummary extends NFLResource
{
    public int|string|null $eventId = null;

    public function setCacheFilePath()
    {
        $dirs = ['events'];

        $file = [
            'event',
            $this->eventId,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/summary/event-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('summary');

        $response = $this->get($url, $this->query([
            'event' => $this->eventId,
        ]));

        return $this->returnResponse($response);
    }
}
