<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class RequestedSeasonData extends BaseData
{
    public function __construct(
        public ?int $year = null,
        public ?int $type = null,
        public ?string $name = null,
        public ?string $displayName = null,
    ) {
        //
    }
}
