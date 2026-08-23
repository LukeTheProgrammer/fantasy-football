<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

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
