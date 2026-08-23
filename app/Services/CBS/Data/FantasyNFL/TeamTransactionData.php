<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class TeamTransactionData extends BaseData
{
    public function __construct(
        public ?int $acquisitionBudgetSpent = null,
        public ?int $acquisitions = null,
        public ?int $drops = null,
        public ?int $misc = null,
        public ?int $moveToActive = null,
        public ?int $moveToIR = null,
        public ?int $paid = null,
        public ?int $teamCharges = null,
        public ?int $trades = null,
        public array $matchupAcquisitionTotals = [],
    ) {
        //
    }
}
