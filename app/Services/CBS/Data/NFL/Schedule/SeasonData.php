<?php

namespace App\Services\CBS\Data\NFL\Schedule;

use App\Services\CBS\Data\BaseData;

class SeasonData extends BaseData
{
    public function __construct(
        public ?int $half = null,
        public ?int $type = null,
        public ?int $year = null,
        public ?string $displayName = null,
        public ?string $name = null,
    ) {
        //
    }
}
