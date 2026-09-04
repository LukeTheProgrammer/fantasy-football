<?php

namespace App\Services\Rankings\Actions;

use App\Models\DraftRanking;
use App\Models\League;

/**
 * The closest scoring format to this league's own that rankings were actually
 * published in.
 *
 * Sources do not publish every combination. Superflex changes a draft board far
 * more than a half point per reception does, so it is matched first.
 */
class ResolveFormatAction
{
    /**
     * @return array{0: float, 1: bool}
     */
    public function run(League $league): array
    {
        $ppr = $league->settings?->pprValue() ?? 0.0;
        $superflex = (bool) $league->settings?->two_qb;

        $available = DraftRanking::query()
            ->availableFormats($league->season_id)
            ->get()
            ->map(fn (DraftRanking $ranking) => [(float) $ranking->ppr, (bool) $ranking->superflex]);

        if ($available->isEmpty()) {
            return [$ppr, $superflex];
        }

        $preferences = [
            fn ($format) => $format === [$ppr, $superflex],
            fn ($format) => $superflex && $format[1] === true,
            fn ($format) => $format[0] === $ppr && $format[1] === false,
        ];

        foreach ($preferences as $matches) {
            $match = $available->first($matches);

            if ($match) {
                return $match;
            }
        }

        return $available->first();
    }
}
