<?php

namespace App\Services\Espn\Extractors;

use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class FantasyDraftExtractor
{
    public static function from(mixed $data)
    {
        if ($data instanceof Response) {
            return self::fromArray($data->json());
        }

        if ($data instanceof Collection) {
            return self::fromArray($data->toArray());
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data)
    {
        return ResourceLeagueData::from($data);
    }
}
