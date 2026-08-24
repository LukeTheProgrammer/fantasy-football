<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class SettingsSettingsData extends BaseData
{
    public function __construct(
        public ?bool $isAutoReactivate = null,
        public ?bool $isCustomizable = null,
        public ?bool $isPublic = null,
        public ?int $size = null,
        public ?string $name = null,
        public ?string $restrictionType = null,

        #[WithCast(AcquisitionSettingsData::class)]
        public array|AcquisitionSettingsData $acquisitionSettings = [],

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(DraftSettingsData::class)]
        public array|DraftSettingsData $draftSettings = [],

        #[WithCast(FinancialSettingsData::class)]
        public array|FinancialSettingsData $financeSettings = [],

        #[WithCast(RosterSettingsData::class)]
        public array|RosterSettingsData $rosterSettings = [],

        #[WithCast(ScheduleSettingsData::class)]
        public array|ScheduleSettingsData $scheduleSettings = [],

        #[WithCast(ScoringSettingsData::class)]
        public array|ScoringSettingsData $scoringSettings = [],

        #[WithCast(TradeSettingsData::class)]
        public array|TradeSettingsData $tradeSettings = [],
    ) {
        //
    }
}
