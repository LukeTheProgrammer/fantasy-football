<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class DraftDetailData extends BaseData
{
    public function __construct(
        public ?bool $drafted = null,
        public ?bool $inProgress = null,
        public ?int $completeDate = null,

        #[WithCast(DraftPickData::class)]
        public array|Collection $picks = [],
    ) {
        //
    }
}
