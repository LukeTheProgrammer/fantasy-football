<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

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
