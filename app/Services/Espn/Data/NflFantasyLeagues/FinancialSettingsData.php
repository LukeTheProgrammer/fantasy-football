<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class FinancialSettingsData extends BaseData
{
    public function __construct(
        public ?int $entryFee = null,
        public ?int $miscFee = null,
        public ?int $perLoss = null,
        public ?int $perTrade = null,
        public ?int $playerAcquisition = null,
        public ?int $playerDrop = null,
        public ?int $playerMoveToActive = null,
        public ?int $playerMoveToIR = null,
    ) {
        //
    }
}
