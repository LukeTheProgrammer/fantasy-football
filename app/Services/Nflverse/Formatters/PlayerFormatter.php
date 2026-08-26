<?php

namespace App\Services\Nflverse\Formatters;

use App\Enums\NFLPositions;
use App\Enums\NFLTeams;
use Illuminate\Support\Arr;

/**
 * Maps a row of the nflverse player list onto the app's players table.
 *
 * The value of this file is the ids: one row carries the NFL's gsis id beside
 * the Pro Football Reference and ESPN ids, which is what links a player the app
 * already knows to the stat lines arriving under a different name.
 */
class PlayerFormatter
{
    /**
     * @param array<string, string> $row
     *
     * @return array<string, mixed>
     */
    public function format(array $row): array
    {
        return [
            'gsis_id'       => $this->value($row, 'gsis_id'),
            'pfr_id'        => $this->value($row, 'pfr_id'),
            'espn_id'       => $this->value($row, 'espn_id'),
            'full_name'     => $this->value($row, 'display_name'),
            'first_name'    => $this->value($row, 'first_name'),
            'last_name'     => $this->value($row, 'last_name'),
            'position_id'   => $this->position($row),
            'team_id'       => NFLTeams::fromAbbreviation($this->value($row, 'latest_team'))?->value,
            'jersey_number' => $this->value($row, 'jersey_number'),
            'height'        => $this->height($row),
            'weight'        => $this->weight($row),
            'college'       => $this->value($row, 'college_name'),
            'birth_date'    => $this->value($row, 'birth_date'),
            'headshot'      => $this->value($row, 'headshot'),
            'draft_year'    => $this->value($row, 'draft_year'),
            'draft_round'   => $this->value($row, 'draft_round'),
            'draft_pick'    => $this->value($row, 'draft_pick'),
            'draft_team'    => $this->value($row, 'draft_team'),
            // Not app columns, but the importer needs them to decide whether a
            // player is worth creating.
            'rookie_season' => $this->value($row, 'rookie_season'),
            'last_season'   => $this->value($row, 'last_season'),
            'status'        => $this->value($row, 'status'),
        ];
    }

    /**
     * Height arrives in inches; the app has always stored it as feet and
     * inches, and that is what the draft room renders.
     */
    private function height(array $row): ?string
    {
        $inches = (int) $this->value($row, 'height');

        return $inches > 0 ? intdiv($inches, 12) . "' " . ($inches % 12) . '"' : null;
    }

    private function weight(array $row): ?string
    {
        $pounds = (int) $this->value($row, 'weight');

        return $pounds > 0 ? $pounds . ' lbs' : null;
    }

    /**
     * nflverse uses its own labels for a few spots on the field.
     */
    private function position(array $row): ?string
    {
        $position = $this->value($row, 'position');

        return match (strtoupper((string) $position)) {
            'SAF'     => NFLPositions::S->value,
            'OL', 'T' => NFLPositions::OT->value,
            'NT'      => NFLPositions::DT->value,
            'HB'      => NFLPositions::RB->value,
            'DB'      => NFLPositions::CB->value,
            default   => $position,
        };
    }

    private function value(array $row, string $key): ?string
    {
        $value = trim((string) Arr::get($row, $key, ''));

        return $value === '' || strtoupper($value) === 'NA' ? null : $value;
    }
}
