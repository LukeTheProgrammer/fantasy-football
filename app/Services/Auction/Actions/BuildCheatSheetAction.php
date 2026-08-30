<?php

namespace App\Services\Auction\Actions;

use App\Enums\Datum;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Services\Auction\Helpers\ByeWeeks;
use App\Services\Auction\Helpers\PreviousAuction;
use Illuminate\Support\Collection;

/**
 * One row per rankable player, carrying everything the sheet shows: where he
 * ranks, what he projects, both value estimates, and what this league paid for
 * him last time.
 */
class BuildCheatSheetAction
{
    /**
     * How many of each position to borrow when the league's own board omits
     * them. Twenty is two per team: enough that the run never empties before
     * everyone has one, without burying the sheet in kickers.
     */
    private const FILL_LIMIT = 20;

    /**
     * The positions FantasyPros leaves off its superflex board.
     */
    private const FILL_POSITIONS = ['K', 'DST'];

    public function run(Draft $draft): Collection
    {
        $league = $draft->league;

        $rankings = $this->rankings($draft);

        if ($rankings->isEmpty()) {
            return collect();
        }

        $projections = (new CalculateProjectedValuesAction)->projectedPoints($draft);

        $marketValues = (new CalculateMarketValuesAction)->run($draft);
        $positionValues = (new CalculateMarketValuesAction)->byPosition($draft);
        $positionRanks = $this->positionRanks($rankings);
        $projectedValues = (new CalculateProjectedValuesAction)->run($draft, $projections);
        $previousPrices = $this->previousPrices($draft);
        $projectedPoints = $projections->mapWithKeys(fn ($player) => [
            $player['player_id'] => round($player['points'], 1),
        ]);
        $averageValues = $this->averageDraftValues($draft);
        $drafted = $draft->picks->keyBy('player_id');
        $byeWeeks = ByeWeeks::for($league->season_id);

        return $rankings->map(function (DraftRanking $ranking) use (
            $marketValues,
            $positionValues,
            $positionRanks,
            $projectedValues,
            $previousPrices,
            $projectedPoints,
            $averageValues,
            $drafted,
            $byeWeeks,
            $league
        ) {
            $player = $ranking->player;
            $pick = $drafted->get($ranking->player_id);

            return [
                'player_id'        => $ranking->player_id,
                'full_name'        => $player?->full_name,
                'position_id'      => $player?->position_id,
                'team_id'          => $player?->team_id,
                'bye_week'         => $player?->team_id ? $byeWeeks->get($player->team_id) : null,
                'headshot'         => $player?->headshot,
                'rank'             => $ranking->rank,
                'tier'             => $ranking->tier,
                'projected_points' => $projectedPoints->get($ranking->player_id),
                'market_value'     => $this->marketValue(
                    $marketValues,
                    $positionValues,
                    $positionRanks,
                    $ranking,
                    $player?->position_id,
                ),
                'projected_value' => $projectedValues->get($ranking->player_id),
                'previous_price'  => $previousPrices->get($ranking->player_id),
                'adv'             => $averageValues->get($ranking->player_id),
                'drafted_by'      => $pick?->league_member_id,
                'drafted_for'     => $pick ? (int) $pick->amount : null,
                'pick_id'         => $pick?->id,
                'season'          => $league->season_id,
            ];
        })->values();
    }

    /**
     * Where each player sits among his own position on this year's board.
     *
     * @param Collection<int, DraftRanking> $rankings
     *
     * @return Collection<int, int> Positional rank keyed by player id.
     */
    private function positionRanks(Collection $rankings): Collection
    {
        $counts = [];

        return $rankings->mapWithKeys(function (DraftRanking $ranking) use (&$counts) {
            $position = $ranking->player?->position_id ?? '';

            $counts[$position] = ($counts[$position] ?? 0) + 1;

            return [$ranking->player_id => $counts[$position]];
        });
    }

    /**
     * What this room has paid for a player of this rank.
     *
     * The position's own curve is what the room actually bids against; the
     * overall curve stands in where the position has no history that deep.
     *
     * @param Collection<int, float> $marketValues
     * @param Collection<string, Collection<int, float>> $positionValues
     * @param Collection<int, int> $positionRanks
     */
    private function marketValue(
        Collection $marketValues,
        Collection $positionValues,
        Collection $positionRanks,
        DraftRanking $ranking,
        ?string $position,
    ): ?float {
        $positional = $position
            ? $positionValues->get($position)?->get($positionRanks->get($ranking->player_id))
            : null;

        return $positional ?? $marketValues->get($ranking->rank);
    }

    /**
     * The newest rankings in the format this league is scored under.
     */
    private function rankings(Draft $draft): Collection
    {
        $league = $draft->league;

        $season = $league->season_id;
        $ppr = $league->settings?->pprValue() ?? 0.0;
        $superflex = (bool) $league->settings?->two_qb;

        $available = DraftRanking::query()
            ->availableFormats($season)
            ->get()
            ->map(fn (DraftRanking $ranking) => [(float) $ranking->ppr, (bool) $ranking->superflex]);

        // Superflex reshapes a draft board more than the reception value does,
        // so it is matched before the scoring format.
        $format = $available->first(fn ($candidate) => $candidate === [$ppr, $superflex])
            ?? $available->first(fn ($candidate) => $superflex && $candidate[1] === true)
            ?? $available->first(fn ($candidate) => $candidate[0] === $ppr && $candidate[1] === false)
            ?? $available->first();

        if (!$format) {
            return collect();
        }

        $rankings = DraftRanking::query()
            ->latestRanking($season, $format[0], $format[1])
            ->forFormat($format[0], $format[1])
            ->where('rank', '>', 0)
            ->with(['player:id,full_name,position_id,team_id,headshot'])
            ->orderBy('rank')
            ->get();

        return $rankings->concat($this->fillPositions($rankings, $season, $ppr, $format));
    }

    /**
     * The kickers and defenses the chosen board leaves out.
     *
     * FantasyPros publishes no kicker or defense on its superflex board, so a
     * superflex league loses the two positions it still has to fill. Neither
     * scores a reception, which is the only thing the scoring formats disagree
     * about here, so the league's own scoring board ranks them exactly as a
     * superflex one would and is borrowed from without adjustment.
     *
     * They are renumbered onto the end of the board rather than keeping the
     * rank they held on a longer one -- a defense ranked 185th of 939 is not
     * the 185th player on a 524 player sheet -- which also leaves them past
     * the end of the price curve, where a market value would be invented
     * rather than measured.
     *
     * @param Collection<int, DraftRanking> $rankings
     * @param array{0: float, 1: bool} $format
     *
     * @return Collection<int, DraftRanking>
     */
    private function fillPositions(Collection $rankings, int $season, float $ppr, array $format): Collection
    {
        $missing = collect(self::FILL_POSITIONS)
            ->reject(fn (string $position) => $rankings->contains(
                fn (DraftRanking $ranking) => $ranking->player?->position_id === $position
            ));

        if ($missing->isEmpty()) {
            return collect();
        }

        $rank = (int) $rankings->max('rank');

        return $missing->flatMap(function (string $position) use ($season, $ppr, $format, &$rank) {
            return $this->rankedAtPosition($season, $ppr, $format, $position)
                ->map(function (DraftRanking $ranking) use (&$rank) {
                    // In memory only: the sheet is read, never written back.
                    $ranking->rank = ++$rank;

                    return $ranking;
                });
        });
    }

    /**
     * The top of one position from the newest board that ranks it.
     *
     * The league's own scoring format is preferred; any board that carries the
     * position stands in when it has none, since a sheet missing the position
     * entirely is worse than one ranking it under another format.
     *
     * @param array{0: float, 1: bool} $format
     *
     * @return Collection<int, DraftRanking>
     */
    private function rankedAtPosition(int $season, float $ppr, array $format, string $position): Collection
    {
        $candidates = collect([[$ppr, false]])
            ->concat(DraftRanking::query()
                ->availableFormats($season)
                ->get()
                ->map(fn (DraftRanking $ranking) => [(float) $ranking->ppr, (bool) $ranking->superflex]))
            ->reject(fn (array $candidate) => $candidate === $format)
            ->unique(fn (array $candidate) => $candidate[0] . ':' . $candidate[1]);

        foreach ($candidates as $candidate) {
            $ranked = DraftRanking::query()
                ->latestRanking($season, $candidate[0], $candidate[1])
                ->forFormat($candidate[0], $candidate[1])
                ->where('rank', '>', 0)
                ->whereHas('player', fn ($query) => $query->where('position_id', $position))
                ->with(['player:id,full_name,position_id,team_id,headshot'])
                ->orderBy('rank')
                ->limit(self::FILL_LIMIT)
                ->get();

            if ($ranked->isNotEmpty()) {
                return $ranked;
            }
        }

        return collect();
    }

    /**
     * What this league paid for each player in its last auction.
     *
     * @return Collection<int, int> Keyed by player id.
     */
    private function previousPrices(Draft $draft): Collection
    {
        $previous = PreviousAuction::for($draft);

        if (!$previous instanceof Draft) {
            return collect();
        }

        return $previous->picks()
            ->whereNotNull('player_id')
            ->get()
            ->mapWithKeys(fn (DraftPick $pick) => [$pick->player_id => (int) $pick->amount]);
    }

    /**
     * What the wider market pays for each player, from ESPN's newest board for
     * this season.
     *
     * Stored exactly as the source publishes it, so a superflex league reads a
     * single quarterback league's prices at the position and should know it.
     *
     * @return Collection<int, float> Keyed by player id.
     */
    private function averageDraftValues(Draft $draft): Collection
    {
        $season = $draft->league->season_id;

        return DraftRanking::query()
            ->where('season', $season)
            ->fromSource(Datum::SOURCE_ESPN->value)
            ->where('ranked_at', DraftRanking::query()
                ->where('season', $season)
                ->fromSource(Datum::SOURCE_ESPN->value)
                ->max('ranked_at'))
            ->where('adv', '>', 0)
            ->pluck('adv', 'player_id')
            ->map(fn ($value) => round((float) $value));
    }
}
