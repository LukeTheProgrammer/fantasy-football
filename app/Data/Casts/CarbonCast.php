<?php

namespace App\Data\Casts;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class CarbonCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Carbon|null
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        try {
            $dt = Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            return null;
        }

        return $dt;
    }
}
