<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class EventWeekData extends BaseData
{
    public function __construct(
        public ?int $number = null,
        public ?string $text = null,
    ) {
        //
    }
}
