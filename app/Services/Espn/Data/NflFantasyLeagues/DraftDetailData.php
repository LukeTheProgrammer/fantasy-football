<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class DraftDetailData extends BaseData
{
    public function __construct(
        public ?bool $drafted = null,
        public ?bool $inProgress = null,
    ) {
        //
    }
}
