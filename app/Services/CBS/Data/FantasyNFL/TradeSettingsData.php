<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class TradeSettingsData extends BaseData
{
    public function __construct(
        public ?bool $allowOutOfUniverse = null,
        public ?int $deadlineDate = null,
        public ?int $max = null,
        public ?int $revisionHours = null,
        public ?int $vetoVotesRequired = null,
    ) {
        //
    }
}
