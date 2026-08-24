<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class CompetitorScoreData extends BaseData
{
    public function __construct(
        public ?int $value = null,
        public ?string $displayValue = null,
    ) {
        //
    }
}
