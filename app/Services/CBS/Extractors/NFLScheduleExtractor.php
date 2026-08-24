<?php

namespace App\Services\CBS\Extractors;

use App\Services\CBS\Data\NFL\Schedule\ScheduleResourceData;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;

class NFLScheduleExtractor
{
    public static function from(mixed $data)
    {
        if (is_array($data)) {
            return self::fromArray($data);
        }

        if ($data instanceof Collection) {
            return self::fromArray($data->toArray());
        }

        if ($data instanceof Response) {
            return self::fromArray($data->json());
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data)
    {
        return ScheduleResourceData::from($data);
    }
}
