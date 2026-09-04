<?php

namespace App\Services\Rosters;

use App\Services\Rosters\Actions\SlotLineupAction;
use Illuminate\Support\Collection;

/**
 * The roster shape a league plays, and how a squad fills it.
 *
 * Both draft rooms lay a squad out the same way; only what they sort it by
 * differs, so the placing lives here and the ordering stays with the caller.
 */
class RosterService
{
    /**
     * @param array<int, string> $template
     * @param Collection<int, array<string, mixed>> $squad
     *
     * @return array<int, array<string, mixed>>
     */
    public function slotLineup(array $template, Collection $squad): array
    {
        return (new SlotLineupAction)->run($template, $squad);
    }

    /**
     * @return array<int, string>
     */
    public function reserveSlots(): array
    {
        return SlotLineupAction::RESERVE_SLOTS;
    }
}
