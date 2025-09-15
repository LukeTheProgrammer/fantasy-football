<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

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
