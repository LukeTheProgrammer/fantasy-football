<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class DraftSettingsData extends BaseData
{
    public function __construct(
        public ?bool $isTradingEnabled = null,
        public ?int $auctionBudget = null,
        public ?int $availableDate = null,
        public ?int $date = null,
        public ?int $keeperCount = null,
        public ?int $keeperCountFuture = null,
        public ?int $timePerSelection = null,
        public ?string $keeperOrderType = null,
        public ?string $leagueSubType = null,
        public ?string $orderType = null,
        public ?string $type = null,
        public array $pickOrder = [],
    ) {
        //
    }
}
