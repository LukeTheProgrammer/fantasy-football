<?php

namespace App\Services\Espn\Helpers;

use App\Services\Espn\Data\FantasyNFL\DraftDetailData;
use Illuminate\Support\Collection;

/**
 * ESPN's draft picks in the shape the draft_picks table is written in.
 *
 * Both the full league import and the live draft sync read the same
 * mDraftDetail payload, so the mapping between them lives in one place.
 */
class DraftPickMapper
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function map(DraftDetailData $draftDetail): array
    {
        $picks = $draftDetail->picks;

        $picks = ($picks instanceof Collection) ? $picks : collect($picks);

        // A live draft returns every slot on the board, the unfilled ones
        // carrying playerId and teamId of -1. Only -1: a team defense is
        // legitimately a negative id (-16000 - teamId), so the test cannot be
        // a sign test.
        $picks = $picks->reject(
            fn ($pick) => $pick->playerId === -1 || $pick->teamId === -1
        );

        return $picks->map(fn ($pick) => [
            'league_member_id'    => $pick->teamId,
            'player_id'           => $pick->playerId,
            'round'               => $pick->roundId,
            'pick_number'         => $pick->roundPickNumber,
            'overall_pick_number' => $pick->overallPickNumber,
            'amount'              => $pick->bidAmount,
            'is_keeper'           => $pick->keeper,
        ])->values()->all();
    }
}
