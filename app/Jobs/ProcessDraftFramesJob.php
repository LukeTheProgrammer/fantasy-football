<?php

namespace App\Jobs;

use App\Events\DraftPicksSynced;
use App\Facades\Auction;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Services\Espn\Helpers\DraftFrameParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Turn a batch of draft-socket frames into picks and nominations.
 *
 * The browser extension posts frames as they arrive, and the post has to
 * return immediately: a player lookup that runs long must not stall the draft
 * room's tap. So the request only queues, and the work happens here.
 */
class ProcessDraftFramesJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, array<string, mixed>> $frames
     */
    public function __construct(
        public int $espnLeagueId,
        public array $frames,
    ) {
        //
    }

    public function handle(): void
    {
        $league = League::where('platform_id', $this->espnLeagueId)
            ->latest('season_id')
            ->first();

        $draft = $league?->draft;

        if (!$draft instanceof Draft) {
            return;
        }

        $recorded = 0;

        foreach ($this->frames as $frame) {
            $sold = DraftFrameParser::sold((string) $frame);

            if ($sold !== null) {
                if (Auction::recordSoldPick($draft, $sold) instanceof DraftPick) {
                    $recorded++;
                }

                // The sale ends the nomination it belongs to.
                Auction::clearNomination($draft);

                continue;
            }

            $bid = DraftFrameParser::bid((string) $frame);

            if ($bid !== null) {
                Auction::recordNomination($draft, $bid);
            }
        }

        // The room reloads on this event, so it is only sent when the board
        // actually changed.
        if ($recorded > 0) {
            DraftPicksSynced::dispatch($draft, $recorded);
        }
    }
}
