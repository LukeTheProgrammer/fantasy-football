<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class EventSeasonData extends BaseData
{
    public function __construct(
        public ?int $year = null,
        public ?string $displayName = null,
    ) {
        //
    }
}
