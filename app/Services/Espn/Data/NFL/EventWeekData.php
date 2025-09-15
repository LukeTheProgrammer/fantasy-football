<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class EventWeekData extends BaseData
{
    public function __construct(
        public ?int $number = null,
        public ?string $text = null,
    ) {
        //
    }
}
