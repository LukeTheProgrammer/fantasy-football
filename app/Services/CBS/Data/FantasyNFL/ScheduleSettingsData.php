<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class ScheduleSettingsData extends BaseData
{
    public function __construct(
        public ?bool $playoffReseed = null,
        public ?bool $variablePlayoffMatchupPeriodLength = null,
        public ?int $matchupPeriodCount = null,
        public ?int $matchupPeriodLength = null,
        public ?int $periodTypeId = null,
        public ?int $playoffMatchupPeriodLength = null,
        public ?int $playoffSeedingRuleBy = null,
        public ?int $playoffTeamCount = null,
        public ?string $playoffSeedingRule = null,
        public array $divisions = [],
        public array $matchupPeriods = [],
    ) {
        //
    }
}
