<?php

namespace App\Services\Espn\Formatters;

use App\Models\League;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Collection;

class FantasyLineupFormatter
{
    private array $positionIds = [];

    public function __construct(private League $league, private array|Collection $roster)
    {
        $this->roster = collect($this->roster);

        $this->positionIds = array_flip(EspnConstants::POSITION_SLOT_MAP);
    }

    public static function from(League $league, array|Collection $roster)
    {
        $formatter = new FantasyLineupFormatter($league, $roster);

        return $formatter->format();
    }

    public function format()
    {
        $lineup = [];
        $roster = $this->roster->toArray(); // Convert to array for easier manipulation

        $positions = $this->league->settings->roster_positions;

        // Iterate through each position in the defined order
        foreach ($positions as $position) {
            // Get the integer ID for this position
            $positionId = $this->positionIds[$position] ?? null;

            if ($positionId === null) {
                continue; // Skip if position not found in mapping
            }

            // Find the first player that matches this position ID
            foreach ($roster as $index => $player) {
                if (isset($player['lineup_slot_id']) && $player['lineup_slot_id'] == $positionId) {
                    // Add player to lineup
                    $lineup[] = $player;

                    // Remove player from roster so they can't be used again
                    unset($roster[$index]);

                    // Break to only get the first matching player for this position
                    break;
                }
            }
        }

        return $lineup;
    }
}
