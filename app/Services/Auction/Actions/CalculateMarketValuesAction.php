<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Services\Auction\Helpers\PreviousAuction;
use Illuminate\Support\Collection;

/**
 * Prices this year's ranks against the shape of last year's spending.
 *
 * A league's auction has a personality: how top heavy it is, where the bidding
 * flattens out, how many players go for a dollar. Rather than model that, this
 * takes the actual sorted prices from the league's previous auction and reads
 * this year's rank off the same curve.
 */
class CalculateMarketValuesAction
{
    /**
     * @return Collection<int, float> Dollar value keyed by overall rank.
     */
    public function run(Draft $draft): Collection
    {
        $prices = $this->previousPrices($draft);

        if ($prices->isEmpty()) {
            return collect();
        }

        return $prices->values()->mapWithKeys(fn ($price, $index) => [$index + 1 => (float) $price]);
    }

    /**
     * Every amount paid in this league's last completed auction, highest first.
     */
    private function previousPrices(Draft $draft): Collection
    {
        $previous = PreviousAuction::for($draft);

        if (!$previous instanceof Draft) {
            return collect();
        }

        return $previous->picks()
            ->whereNotNull('amount')
            ->where('amount', '>', 0)
            ->orderByDesc('amount')
            ->pluck('amount')
            ->map(fn ($amount) => (float) $amount);
    }
}
