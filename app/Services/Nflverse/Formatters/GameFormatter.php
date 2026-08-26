<?php

namespace App\Services\Nflverse\Formatters;

use App\Enums\NFLTeams;
use App\Enums\SeasonType;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Maps a row of the nflverse schedule onto the app's nfl_games table.
 *
 * Games already in the table arrived from ESPN, so the espn id is what matches
 * them; the nflverse and Pro Football Reference ids are recorded beside it so a
 * stat line or a box score can find the same game later.
 */
class GameFormatter
{
    /**
     * @param array<string, string> $row
     *
     * @return array<string, mixed>
     */
    public function format(array $row): array
    {
        $kickoff = $this->value($row, 'gameday');
        $time = $this->value($row, 'gametime');

        return [
            'nflverse_id'  => $this->value($row, 'game_id'),
            'espn_id'      => $this->value($row, 'espn'),
            'pfr_id'       => $this->value($row, 'pfr'),
            'season'       => (int) $this->value($row, 'season'),
            'week'         => (int) $this->value($row, 'week'),
            'season_type'  => SeasonType::fromSource($this->value($row, 'game_type')),
            'is_playoff'   => SeasonType::fromSource($this->value($row, 'game_type')) === SeasonType::POST,
            'home_team_id' => NFLTeams::fromAbbreviation($this->value($row, 'home_team'))?->value,
            'away_team_id' => NFLTeams::fromAbbreviation($this->value($row, 'away_team'))?->value,
            'home_score'   => $this->value($row, 'home_score'),
            'away_score'   => $this->value($row, 'away_score'),
            'is_completed' => $this->value($row, 'home_score') !== null,
            'starts_at'    => $this->kickoff($kickoff, $time),
            'is_bye'       => false,
        ];
    }

    /**
     * Kickoff is published in Eastern time, which is the league's clock rather
     * than a timezone the row states, so it is converted before it is stored.
     */
    private function kickoff(?string $day, ?string $time): ?Carbon
    {
        if ($day === null) {
            return null;
        }

        return Carbon::parse(trim($day . ' ' . $time), 'America/New_York')->setTimezone('UTC');
    }

    private function value(array $row, string $key): ?string
    {
        $value = trim((string) Arr::get($row, $key, ''));

        return $value === '' || strtoupper($value) === 'NA' ? null : $value;
    }
}
