<?php

namespace App\Actions\Models\PlayerTeam;

use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Season;
use App\Models\Team;

class PlayerTeamUpsertAction
{
    /**
     * Record that a player was on a team in a season.
     *
     * The season defaults to the current one, which is what every caller meant
     * before the column existed. A player who was traded ends up with a row per
     * team for that season rather than one row that keeps being overwritten.
     */
    public function run(
        int|string|Player $player,
        int|string|Team $team,
        int|string|Season|null $season = null,
        ?string $source = null,
    ): PlayerTeam {
        $player = ($player instanceof Player) ? $player : Player::findOrFail($player);
        $team = ($team instanceof Team) ? $team : Team::findOrFail($team);
        $season = $this->season($season);

        // Only one team can be a player's current team, and only the season
        // being imported can say which. Sweeping every season would erase his
        // history every time a past roster is imported.
        PlayerTeam::where('player_id', $player->id)
            ->where('season', $season)
            ->update(['is_current_team' => false]);

        return PlayerTeam::updateOrCreate(
            [
                'player_id' => $player->id,
                'team_id'   => $team->id,
                'season'    => $season,
            ],
            array_filter([
                'is_current_team' => true,
                'source'          => $source,
            ], fn ($value) => $value !== null)
        );
    }

    private function season(int|string|Season|null $season): ?int
    {
        if ($season instanceof Season) {
            return (int) $season->id;
        }

        if (!empty($season)) {
            return (int) $season;
        }

        return Season::current()->first()?->id;
    }
}
