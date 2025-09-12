<?php

namespace App\Services\Espn\Data\FantasyNFL;

use Spatie\LaravelData\Attributes\WithCast;

class TeamRecordData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?float $percentage = null,
        public ?int $gamesBack = null,
        public ?int $losses = null,
        public ?int $pointsAgainst = null,
        public ?int $pointsFor = null,
        public ?int $streakLength = null,
        public ?int $ties = null,
        public ?int $wins = null,
        public ?string $streakType = null,
    ) {
        //
    }
}
