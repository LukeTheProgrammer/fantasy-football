<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class CompetitionStatusTypeData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?bool $completed = null,
        public ?string $description = null,
        public ?string $detail = null,
        public ?string $name = null,
        public ?string $shortDetail = null,
        public ?string $state = null,
    ) {
        //
    }
}
