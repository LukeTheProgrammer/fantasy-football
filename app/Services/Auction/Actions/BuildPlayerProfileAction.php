<?php

namespace App\Services\Auction\Actions;

use App\Enums\Datum;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerProjection;
use Illuminate\Support\Collection;

/**
 * Everything worth knowing about one player while he is on the block.
 *
 * The board answers "what is he worth"; this answers "why", and mostly by
 * looking backwards: what this league actually paid for him in past auctions,
 * how tightly the experts agree on him, and what is left behind him at his
 * position if the bidding runs away.
 */
class BuildPlayerProfileAction
{
    /** How many past auctions the price history looks back over. */
    private const SEASONS = 5;

    /**
     * @return array<string, mixed>
     */
    public function run(Draft $draft, Player $player): array
    {
        $sheet = (new BuildCheatSheetAction)->run($draft);
        $row = $sheet->firstWhere('player_id', $player->id);

        return [
            'player'    => $this->bio($draft, $player),
            'valuation' => $this->valuation($draft, $row),
            'prices'    => $this->prices($draft, $player),
            'consensus' => $this->consensus($draft, $player),
            'position'  => $this->positionContext($sheet, $row),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bio(Draft $draft, Player $player): array
    {
        return [
            'id'        => $player->id,
            'full_name' => $player->full_name,
            'position'  => $player->position_id,
            'team'      => $player->team_id,
            'jersey'    => $player->jersey_number,
            'height'    => $player->height,
            'weight'    => $player->weight,
            'college'   => $player->college,
            'headshot'  => $player->headshot,
            'age'       => $player->age,
            'bye_week'  => $this->byeWeek($draft, $player),
        ];
    }

    /**
     * The week this player's team does not play.
     *
     * Byes are held as their own schedule rows rather than inferred from the
     * weeks a team is missing from.
     */
    private function byeWeek(Draft $draft, Player $player): ?int
    {
        if (!$player->team_id) {
            return null;
        }

        return NflGame::query()
            ->where('season', $draft->league->season)
            ->where('is_bye', true)
            ->where('home_team_id', $player->team_id)
            ->value('week');
    }

    /**
     * This year's numbers for the player, straight off the cheat sheet so the
     * dialog can never disagree with the board behind it.
     *
     * @param array<string, mixed>|null $row
     *
     * @return array<string, mixed>
     */
    private function valuation(Draft $draft, ?array $row): array
    {
        $budget = (int) ($draft->auction_budget ?: 0);
        $market = $row['market_value'] ?? null;

        return [
            'rank'             => $row['rank'] ?? null,
            'tier'             => $row['tier'] ?? null,
            'projected_points' => $row['projected_points'] ?? null,
            'market_value'     => $market,
            'projected_value'  => $row['projected_value'] ?? null,
            'adv'              => $row['adv'] ?? null,
            'drafted_for'      => $row['drafted_for'] ?? null,
            // What the market price costs out of one team's whole budget, which
            // is the number that actually decides whether a bid is affordable.
            'budget_share' => $budget > 0 && $market ? round($market / $budget * 100) : null,
        ];
    }

    /**
     * What this league paid for the player in each of its last auctions.
     *
     * Every season is listed, price or not: the seasons he went undrafted are
     * as much of the story as the seasons he did not.
     *
     * @return array<int, array<string, mixed>>
     */
    private function prices(Draft $draft, Player $player): array
    {
        $leagues = League::query()
            ->sameLeagueAs($draft->league)
            ->where('season', '<', $draft->league->season)
            ->with(['draft', 'members'])
            ->orderByDesc('season')
            ->limit(self::SEASONS)
            ->get()
            ->filter(fn (League $league) => $league->draft !== null);

        $picks = DraftPick::query()
            ->whereIn('draft_id', $leagues->pluck('draft.id'))
            ->where('player_id', $player->id)
            ->get()
            ->keyBy('draft_id');

        return $leagues->sortBy('season')
            ->map(function (League $league) use ($picks) {
                $pick = $picks->get($league->draft->id);

                return [
                    'season' => (int) $league->season,
                    'amount' => $pick ? (int) $pick->amount : null,
                    'team'   => $pick
                        ? $league->members->firstWhere('id', $pick->league_member_id)?->team_name
                        : null,
                    // The most expensive player of that auction, so a price can
                    // be read against the money that was in the room.
                    'top' => (int) DraftPick::query()->where('draft_id', $league->draft->id)->max('amount'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * How much the experts disagree about him, taken from the spread of the
     * position ranks behind the consensus projection.
     *
     * @return array<string, mixed>|null
     */
    private function consensus(Draft $draft, Player $player): ?array
    {
        $ppr = $draft->league->settings?->pprValue() ?? 0.0;

        $rows = PlayerProjection::query()
            ->where('player_id', $player->id)
            ->forSeason($draft->league->season)
            ->fromSource(Datum::SOURCE_FANTASY_PROS)
            ->where('superflex', false)
            ->get();

        $row = $rows->firstWhere('ppr', $ppr) ?? $rows->sortBy('ppr')->first();

        if (!$row || $row->pos_rank === null) {
            return null;
        }

        return [
            'pos_rank' => (int) $row->pos_rank,
            'min'      => $row->pos_rank_min !== null ? (int) $row->pos_rank_min : null,
            'max'      => $row->pos_rank_max !== null ? (int) $row->pos_rank_max : null,
            'average'  => $row->pos_rank_avg !== null ? round((float) $row->pos_rank_avg, 1) : null,
            'std'      => $row->pos_rank_std !== null ? round((float) $row->pos_rank_std, 2) : null,
        ];
    }

    /**
     * Who else is left at his position: the cliff behind him, and how thin his
     * tier has become. Both are reasons to pay up, or reasons to wait.
     *
     * @param Collection<int, array<string, mixed>> $sheet
     * @param array<string, mixed>|null $row
     *
     * @return array<string, mixed>
     */
    private function positionContext(Collection $sheet, ?array $row): array
    {
        if (!$row) {
            return ['rank' => null, 'tier_left' => null, 'next' => []];
        }

        $available = $sheet
            ->where('position_id', $row['position_id'])
            ->filter(fn (array $player) => $player['drafted_by'] === null || $player['player_id'] === $row['player_id'])
            ->sortBy('rank')
            ->values();

        $index = $available->search(fn (array $player) => $player['player_id'] === $row['player_id']);

        return [
            // Where he sits among the players at his position still on the board.
            'rank'      => $index === false ? null : $index + 1,
            'tier_left' => $available
                ->where('tier', $row['tier'])
                ->where('player_id', '!=', $row['player_id'])
                ->count(),
            // The next three at his position, which is what passing on him buys.
            'next' => $index === false ? [] : $available->slice($index + 1, 3)->map(fn (array $player) => [
                'player_id'    => $player['player_id'],
                'full_name'    => $player['full_name'],
                'rank'         => $player['rank'],
                'tier'         => $player['tier'],
                'market_value' => $player['market_value'],
            ])->values()->all(),
        ];
    }
}
