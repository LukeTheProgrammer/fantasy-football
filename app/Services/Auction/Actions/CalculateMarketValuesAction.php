<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Services\Auction\Helpers\PreviousAuctions;
use Illuminate\Support\Collection;

/**
 * Prices this year's ranks against the shape of the league's past spending.
 *
 * A league's auction has a personality: how top heavy it is, where the bidding
 * flattens out, how many players go for a dollar. Rather than model that, this
 * takes the actual sorted prices from the league's past auctions, averages them
 * rank by rank, and reads this year's rank off the resulting curve.
 *
 * Averaging across seasons rather than copying the last one stops a single odd
 * auction — one year the room overpaid at the top — from setting this year's
 * prices on its own. The average is weighted towards the recent seasons, since
 * the room that drafted last year is closer to the room drafting this year than
 * the one from four years ago.
 */
class CalculateMarketValuesAction
{
    /**
     * What each season counts for against the one after it: the most recent
     * auction carries twice the weight of the one before it, and so back.
     */
    public const RECENCY_DECAY = 0.5;

    /**
     * @return Collection<int, float> Dollar value keyed by overall rank.
     */
    public function run(Draft $draft): Collection
    {
        $curves = $this->curves($draft);

        if ($curves->isEmpty()) {
            return collect();
        }

        // A rank is averaged over the seasons that actually reached it: a
        // shallow auction should not drag the deep end of the curve down.
        $ranks = $curves->map(fn (Collection $curve) => $curve->count())->max();

        return collect(range(1, $ranks))
            ->mapWithKeys(function (int $rank) use ($curves) {
                $price = $this->weightedPrice($curves, $rank);

                return $price === null ? [] : [$rank => $price];
            });
    }

    /**
     * One rank's price, weighted towards the seasons closest to this one.
     *
     * The weights are normalised over the seasons that reached the rank, so a
     * rank only the oldest auction got to is that auction's price rather than a
     * fraction of it.
     *
     * @param Collection<int, Collection<int, float>> $curves
     */
    private function weightedPrice(Collection $curves, int $rank): ?float
    {
        $total = 0.0;
        $weights = 0.0;

        foreach ($curves as $index => $curve) {
            $price = $curve->get($rank);

            if ($price === null) {
                continue;
            }

            // Curves come back newest first, so the exponent is how many
            // seasons back this one is.
            $weight = self::RECENCY_DECAY ** $index;

            $total += $price * $weight;
            $weights += $weight;
        }

        return $weights > 0.0 ? round($total / $weights) : null;
    }

    /**
     * One price curve per past auction, each keyed by rank.
     *
     * @return Collection<int, Collection<int, float>>
     */
    private function curves(Draft $draft): Collection
    {
        $budget = (int) ($draft->auction_budget ?? 0);

        return PreviousAuctions::for($draft)
            ->map(fn (Draft $previous) => $this->curve($previous, $budget))
            ->filter(fn (Collection $curve) => $curve->isNotEmpty())
            ->values();
    }

    /**
     * Every amount paid in one auction, highest first and keyed by rank.
     *
     * Prices are scaled to this year's budget, so a season the league played
     * for a different amount of money still describes the same shape.
     *
     * @return Collection<int, float>
     */
    private function curve(Draft $previous, int $budget): Collection
    {
        $spent = (int) ($previous->auction_budget ?? 0);

        $scale = $budget > 0 && $spent > 0 ? $budget / $spent : 1.0;

        return $previous->picks()
            ->whereNotNull('amount')
            ->where('amount', '>', 0)
            ->orderByDesc('amount')
            ->pluck('amount')
            ->values()
            ->mapWithKeys(fn ($amount, $index) => [$index + 1 => (float) $amount * $scale]);
    }
}
