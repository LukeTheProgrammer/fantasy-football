<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class EventSeasonTypeData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?int $type = null,
        public ?string $name = null,
        public ?string $abbreviation = null,
    ) {
        //
    }
}
