<?php

namespace App\Services\Espn\Data\FantasyNFL;

class DraftDetailData extends BaseData
{
    public function __construct(
        public ?bool $drafted = null,
        public ?bool $inProgress = null,
    ) {
        //
    }
}
