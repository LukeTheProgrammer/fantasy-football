<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitionVenueData extends BaseData
{
    public function __construct(
        public ?string $fullName = null,

        #[WithCast(CompetitionVenueAddressData::class)]
        public array|CompetitionVenueAddressData $address = [],
    ) {
        //
    }
}
