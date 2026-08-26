<?php

namespace App\Observers;

use App\Models\PlayerAlias;
use App\Services\Player\Helpers\NormalizedName;

class PlayerAliasObserver
{
    /**
     * An alias exists to be matched on, so its comparable form is kept in step
     * with the name it was learned from.
     */
    public function saving(PlayerAlias $alias): void
    {
        if ($alias->isDirty('name') || empty($alias->normalized_name)) {
            $alias->normalized_name = NormalizedName::of($alias->name);
        }
    }
}
