<?php

namespace App\Services\Espn\Data\FantasyNFL;

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
