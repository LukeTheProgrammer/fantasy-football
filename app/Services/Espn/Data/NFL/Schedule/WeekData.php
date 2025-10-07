<?php

namespace App\Services\Espn\Data\NFL\Schedule;

use App\Services\Espn\Data\BaseData;

class WeekData extends BaseData
{
    public function __construct(
        public ?int $number = null,
        public ?string $text = null,
    ) {
        //
    }
}
