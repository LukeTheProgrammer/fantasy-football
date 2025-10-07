<?php

namespace App\Services\Espn\Data\NFL\Schedule;

use App\Services\Espn\Data\BaseData;

class SeasonTypeData extends BaseData
{
    public function __construct(
        public ?int $id = null,
        public ?int $type = null,
        public ?string $name = null,
        public ?string $abbreviation = null,
    ) {
        //
    }
}
