<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class CompetitorRecordData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?string $abbreviation = null,
        public ?string $displayName = null,
        public ?string $shortDisplayName = null,
        public ?string $description = null,
        public ?string $type = null,
        public ?string $displayValue = null,
    ) {
        //
    }
}
