<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class EventSeasonData extends BaseData
{
    public function __construct(
        public ?int $year = null,
        public ?string $displayName = null,
    ) {
        //
    }
}
