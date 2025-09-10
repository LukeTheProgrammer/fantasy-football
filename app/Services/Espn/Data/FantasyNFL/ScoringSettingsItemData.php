<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ScoringSettingsItemData extends BaseData
{
    protected bool $isCollectionCast = true;

    #[Computed]
    public ?string $label = null;

    #[Computed]
    public ?float $value = null;

    public function __construct(
        public ?int $statId = null,
        public ?float $points = null,
        public ?int $leagueRanking = null,
        public ?int $leagueTotal = null,
        public ?bool $isReverseItem = null,
        public array $pointsOverrides = [],
    ) {
        $this->value = Arr::get($this->pointsOverrides, '16', $this->points);
        $this->label = is_int($this->statId)
            ? Arr::get(EspnConstants::PLAYER_STATS_MAP, $this->statId, $this->statId)
            : null;
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return collect($value)
            ->filter(fn ($v) => is_int($v['statId']))
            ->map(fn ($v) => static::from($v));
    }
}
