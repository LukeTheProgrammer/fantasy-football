<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class CompetitorScoreData extends BaseData
{
    public function __construct(
        public ?int $value = null,
        public ?string $displayValue = null,
    ) {
        //
    }
}
