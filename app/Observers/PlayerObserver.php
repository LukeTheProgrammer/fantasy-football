<?php

namespace App\Observers;

use App\Models\Player;
use App\Services\Player\Helpers\NormalizedName;
use Illuminate\Support\Str;

class PlayerObserver
{
    /**
     * Handle the Player "creating" event.
     */
    public function creating(Player $player): void
    {
        $player->ulid = $player->ulid || Str::ulid();

        if (empty($player->full_name)) {
            $player->full_name = trim($player->first_name . ' ' . $player->last_name);
        }
    }

    /**
     * Keep the comparable form of the name in step with the name itself.
     */
    public function saving(Player $player): void
    {
        if ($player->isDirty('full_name') || empty($player->normalized_name)) {
            $player->normalized_name = NormalizedName::of($player->full_name);
        }
    }

    /**
     * Handle the Player "created" event.
     */
    public function created(Player $player): void
    {
        //
    }

    /**
     * Handle the Player "updated" event.
     */
    public function updated(Player $player): void
    {
        //
    }

    /**
     * Handle the Player "deleted" event.
     */
    public function deleted(Player $player): void
    {
        //
    }

    /**
     * Handle the Player "restored" event.
     */
    public function restored(Player $player): void
    {
        //
    }

    /**
     * Handle the Player "force deleted" event.
     */
    public function forceDeleted(Player $player): void
    {
        //
    }
}
