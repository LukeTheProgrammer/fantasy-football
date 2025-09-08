<?php

namespace App\Services\Espn\Data\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class CollectionCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Collection
    {
        return collect($value);
    }
}
