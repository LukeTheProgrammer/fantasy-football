<?php

namespace App\Services\Auction\Actions;

use App\Events\PlayerNominated;
use App\Facades\Espn;
use App\Facades\Player as PlayerFacade;
use App\Models\Draft;
use App\Models\LeagueMember;
use App\Models\Player;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Put the player who is up for bid on the board.
 *
 * A bid arrives several times a second and every one of them names the same
 * player, so the last nomination is remembered and a repeat is dropped: the
 * room only has to hear when the player actually changes. The memory is a
 * cache key rather than a column because it is worth nothing once the draft
 * is over.
 */
class RecordNominationAction
{
    /**
     * How long a nomination is remembered. Long enough to outlive the gap
     * between bids, short enough that a draft left open overnight starts
     * clean.
     */
    protected const TTL = 3600;

    /**
     * @param array<string, int|float> $bid
     */
    public function run(Draft $draft, array $bid): ?Player
    {
        if ((int) Cache::get($this->key($draft)) === (int) $bid['player_id']) {
            return null;
        }

        // A team defense is not an athlete id at ESPN, so the id is translated
        // before it is looked up.
        $player = PlayerFacade::find(
            Espn::playerLookup($bid['player_id']),
            ['source' => static::class]
        );

        if (!$player instanceof Player) {
            Log::error('Player not found for bid frame', $bid);

            return null;
        }

        Cache::put($this->key($draft), (int) $bid['player_id'], self::TTL);

        PlayerNominated::dispatch(
            $draft,
            $player,
            $this->member($draft, $bid),
            (float) ($bid['amount'] ?? 0),
        );

        return $player;
    }

    /**
     * A sale ends the nomination it belongs to, so the next bid on the same
     * player -- ESPN reopens one when a sale is undone -- is heard again.
     */
    public function clear(Draft $draft): void
    {
        Cache::forget($this->key($draft));
    }

    protected function member(Draft $draft, array $bid): ?LeagueMember
    {
        return LeagueMember::forLeague($draft->league)
            ->where('external_id', $bid['espn_team_id'])
            ->first();
    }

    protected function key(Draft $draft): string
    {
        return 'draft.' . $draft->id . '.nominated';
    }
}
