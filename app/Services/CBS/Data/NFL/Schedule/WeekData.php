<?php

namespace App\Services\CBS\Data\NFL\Schedule;

use App\Services\CBS\Data\BaseData;

class WeekData extends BaseData
{
    public function __construct(
        public ?int $number = null,
        public ?string $text = null,
    ) {
        //
    }
}
