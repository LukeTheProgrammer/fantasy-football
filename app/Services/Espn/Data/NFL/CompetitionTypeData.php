<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;

class CompetitionTypeData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?string $text = null,
        public ?string $abbreviation = null,
        public ?string $slug = null,
        public ?string $type = null,
    ) {
        //
    }
}
