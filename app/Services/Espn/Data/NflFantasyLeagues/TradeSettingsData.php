<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

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
