<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;

class CompetitionVenueAddressData extends BaseData
{
    public function __construct(
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zipCode = null,
        public ?string $country = null,
    ) {
        //
    }
}
