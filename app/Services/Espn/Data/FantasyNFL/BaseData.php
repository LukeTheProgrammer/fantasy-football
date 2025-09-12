<?php

namespace App\Services\Espn\Data\FantasyNFL;

use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class BaseData extends Data implements Cast
{
    protected bool $isCollectionCast = false;

    /**
     * This allows the data class to be cast as itsself without needing a separate cast class.
     *
     * @param DataProperty $property
     * @param mixed $value
     * @param array $properties
     * @param CreationContext $context
     *
     * @return mixed
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->isCollectionCast
            ? collect($value)->map(fn ($v) => static::from($v))
            : static::from($value);
    }
}
