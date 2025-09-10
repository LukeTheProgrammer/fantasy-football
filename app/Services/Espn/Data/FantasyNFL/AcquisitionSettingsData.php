<?php

namespace App\Services\Espn\Data\FantasyNFL;

class AcquisitionSettingsData extends BaseData
{
    public function __construct(
        public ?bool $isUsingAcquisitionBudget = null,
        public ?bool $matchupLimitPerScoringPeriod = null,
        public ?bool $transactionLockingEnabled = null,
        public ?bool $waiverOrderReset = null,
        public ?int $acquisitionBudget = null,
        public ?int $acquisitionLimit = null,
        public ?int $finalPlaceTransactionEligible = null,
        public ?int $matchupAcquisitionLimit = null,
        public ?int $minimumBid = null,
        public ?int $waiverHours = null,
        public ?int $waiverProcessHour = null,
        public ?string $acquisitionType = null,
        public array $waiverProcessDays = [],
    ) {
        //
    }
}
