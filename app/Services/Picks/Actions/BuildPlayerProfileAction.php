<?php

namespace App\Services\Picks\Actions;

use App\Enums\Datum;
use App\Facades\Auction;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Models\LeagueMemberRoster;
use App\Models\Player;
use App\Models\PlayerStatYearly;

/**
 * The little that is worth knowing about one player mid draft.
 *
 * Fetched on demand rather than shipped with the board, because the room only
 * ever looks at one player at a time and the board already costs it enough.
 */
class BuildPlayerProfileAction
{
    /** How many past seasons the stat line looks back over. */
    private const SEASONS = 3;

    /**
     * @return array<string, mixed>
     */
    public function run(Draft $draft, Player $player, float $ppr, bool $superflex): array
    {
        return [
            'player_id'  => $player->id,
            'full_name'  => $player->full_name,
            'position'   => $player->position_id,
            'team'       => $player->team_id,
            'headshot'   => $player->headshot,
            'jersey'     => $player->jersey_number,
            'age'        => $player->birth_date?->age,
            'ranking'    => $this->ranking($draft, $player, $ppr, $superflex),
            'projection' => Auction::pointsAboveReplacement($draft)->get($player->id),
            'seasons'    => $this->seasons($player, $ppr),
            'owner'      => $this->owner($draft, $player),
        ];
    }

    /**
     * What he actually did, most recent season first.
     *
     * Three seasons is as far back as a draft room reasonably argues: older
     * than that and the man, the offence and the coach have all changed. Only
     * the regular season counts, because a playoff run is four teams' worth of
     * extra games that the other twenty eight never had the chance at.
     *
     * @return array<int, array<string, mixed>>
     */
    private function seasons(Player $player, float $ppr): array
    {
        return PlayerStatYearly::query()
            ->forPlayer($player->id)
            ->regularSeason()
            ->orderByDesc('season')
            ->limit(self::SEASONS)
            ->get()
            ->map(fn (PlayerStatYearly $stat) => $this->seasonLine($stat, $ppr))
            ->all();
    }

    /**
     * One season shaped for the table, in this league's scoring format.
     *
     * nflverse publishes standard and full PPR only, so a half point league
     * is built back up from the standard line rather than rounded to one of
     * the two: the reception points are the whole of the difference.
     *
     * @return array<string, mixed>
     */
    private function seasonLine(PlayerStatYearly $stat, float $ppr): array
    {
        $games = (int) $stat->games_played;
        $points = (float) $stat->fantasy_points + ($ppr * (int) $stat->receiving_receptions);

        return [
            'season'          => (int) $stat->season,
            'team'            => $stat->team_id,
            'games'           => $games,
            'passing_yards'   => (int) $stat->passing_yards,
            'passing_tds'     => (int) $stat->passing_touchdowns,
            'interceptions'   => (int) $stat->passing_interceptions,
            'rushing_carries' => (int) $stat->rushing_attempts,
            'rushing_yards'   => (int) $stat->rushing_yards,
            'rushing_tds'     => (int) $stat->rushing_touchdowns,
            'targets'         => (int) $stat->receiving_targets,
            'receptions'      => (int) $stat->receiving_receptions,
            'receiving_yards' => (int) $stat->receiving_yards,
            'receiving_tds'   => (int) $stat->receiving_touchdowns,
            'points'          => round($points, 1),
            'points_per_game' => $games > 0 ? round($points / $games, 1) : null,
        ];
    }

    /**
     * Where the board has him, in the league's own scoring format.
     *
     * @return array<string, mixed>|null
     */
    private function ranking(Draft $draft, Player $player, float $ppr, bool $superflex): ?array
    {
        $ranking = DraftRanking::query()
            ->latestRanking($draft->league->season_id, $ppr, $superflex)
            ->forFormat($ppr, $superflex)
            ->where('player_id', $player->id)
            ->first();

        if (!$ranking instanceof DraftRanking) {
            return null;
        }

        // ADP and average value are published on ESPN's board alone, so they
        // come from there rather than from the FantasyPros row the rank and
        // tier are read off, which carries neither.
        $market = $this->market($draft, $player);

        return [
            'rank' => $ranking->rank,
            'tier' => $ranking->tier,
            'adp'  => $market?->adp > 0 ? round((float) $market->adp, 1) : null,
            'adv'  => $market?->adv > 0 ? round((float) $market->adv) : null,
        ];
    }

    /**
     * ESPN's newest row for this player, which is where ADP and ADV live.
     */
    private function market(Draft $draft, Player $player): ?DraftRanking
    {
        $season = $draft->league->season_id;

        return DraftRanking::query()
            ->where('season', $season)
            ->fromSource(Datum::SOURCE_ESPN->value)
            ->where('player_id', $player->id)
            ->orderByDesc('ranked_at')
            ->first();
    }

    /**
     * Whose he is, and how they came by him.
     *
     * @return array<string, mixed>|null
     */
    private function owner(Draft $draft, Player $player): ?array
    {
        $pick = $draft->picks()->with('leagueMember')->where('player_id', $player->id)->first();

        if ($pick instanceof DraftPick) {
            return [
                'team_name' => $pick->leagueMember?->team_name,
                'source'    => 'R' . $pick->round . '.' . $pick->pick_number,
            ];
        }

        $keeper = LeagueMemberRoster::with('leagueMember')
            ->whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->where('player_id', $player->id)
            ->first();

        if ($keeper instanceof LeagueMemberRoster) {
            return [
                'team_name' => $keeper->leagueMember?->team_name,
                'source'    => 'Keeper',
            ];
        }

        return null;
    }
}
