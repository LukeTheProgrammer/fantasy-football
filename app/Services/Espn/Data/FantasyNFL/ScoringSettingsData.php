<?php

namespace App\Services\Espn\Data\FantasyNFL;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ScoringSettingsData extends BaseData
{
    public function __construct(
        public ?bool $allowOutOfPositionScoring = null,
        public ?int $homeTeamBonus = null,
        public ?int $matchupTieRuleBy = null,
        public ?int $playoffHomeTeamBonus = null,
        public ?int $playoffMatchupTieRuleBy = null,
        public ?string $matchupTieRule = null,
        public ?string $playerRankType = null,
        public ?string $playoffMatchupTieRule = null,
        public ?string $scoringType = null,

        #[WithCast(ScoringSettingsItemData::class)]
        public array|Collection $scoringItems = [],
    ) {
        //
    }
}
