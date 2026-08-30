<?php

namespace App\Services\Espn\Helpers;

use Illuminate\Support\Str;

/**
 * ESPN's draft socket frames, read into something the app can use.
 *
 * The protocol is undocumented plain text, space delimited, verb first. What
 * is known was read off a live auction:
 *
 *   BID 1 3918298 25 30000 10000
 *   SOLD 1 3918298 1 25 0
 *
 * so a SOLD carries team, player, roster slot and price. The third field is
 * not the sale's position in the draft: across one auction it repeated for
 * different teams (1 for teams 1, 3 and 2) while staying unique within a team,
 * which is a lineup slot, not an order. Nothing in a SOLD says when it
 * happened, so the board numbers its own picks. The trailing field was 0 on
 * every frame captured and is left alone.
 *
 * A team defense arrives as ESPN's negative fantasy id (-16034 for Houston),
 * which Espn::playerLookup() translates.
 *
 * SOLD and BID are read. The board only has to change when a player is sold,
 * but a BID is the only frame that names the player currently up:
 *
 *   AUTOSUGGEST 4429795
 *   NOMINATION 9 15000
 *   BID 9 4426348 1 15000 15000
 *
 * NOMINATION carries the team on the clock and the clock itself, never a
 * player, and AUTOSUGGEST is ESPN's suggestion rather than the nomination --
 * on the batch above the suggested player is not the one that went up. So the
 * opening bid is what a nomination is read from.
 *
 * A BID's third field is the price: it opens at 1 and climbs across the
 * frames of one nomination (1, 14, 20, 24, 27) while the trailing pair counts
 * down, which is the clock. Note this is not where SOLD keeps its amount --
 * there the third field is the lineup slot and the fourth is the price.
 */
class DraftFrameParser
{
    public const SOLD = 'SOLD';

    public const BID = 'BID';

    /**
     * The sale in a frame, or null if the frame is anything else.
     *
     * @return array<string, int|float>|null
     */
    public static function sold(string $frame): ?array
    {
        $parts = preg_split('/\s+/', trim($frame));

        if (count($parts) < 5 || $parts[0] !== self::SOLD) {
            return null;
        }

        [$verb, $teamId, $playerId, $lineupSlot, $amount] = $parts;

        if (!is_numeric($teamId) || !is_numeric($playerId) || !is_numeric($amount)) {
            return null;
        }

        return [
            'espn_team_id' => (int) $teamId,
            'player_id'    => (int) $playerId,
            'lineup_slot'  => (int) $lineupSlot,
            'amount'       => (float) $amount,
        ];
    }

    /**
     * The bid in a frame, or null if the frame is anything else.
     *
     * @return array<string, int|float>|null
     */
    public static function bid(string $frame): ?array
    {
        $parts = preg_split('/\s+/', trim($frame));

        if (count($parts) < 3 || $parts[0] !== self::BID) {
            return null;
        }

        [$verb, $teamId, $playerId, $amount] = array_pad(array_slice($parts, 0, 4), 4, null);

        if (!is_numeric($teamId) || !is_numeric($playerId)) {
            return null;
        }

        return [
            'espn_team_id' => (int) $teamId,
            'player_id'    => (int) $playerId,
            'amount'       => (float) $amount,
        ];
    }

    /**
     * The ESPN league id a socket url belongs to.
     */
    public static function leagueId(?string $url): ?int
    {
        if (!Str::contains((string) $url, 'league-')) {
            return null;
        }

        preg_match('/league-(\d+)/', (string) $url, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }
}
