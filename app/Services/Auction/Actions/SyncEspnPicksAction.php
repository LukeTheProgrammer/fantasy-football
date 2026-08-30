<?php

namespace App\Services\Auction\Actions;

use App\Facades\Action;
use App\Facades\Espn;
use App\Facades\Player as PlayerFacade;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\LeagueMember;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Pull the picks ESPN has recorded for a draft and write the new ones in.
 *
 * This runs repeatedly while a draft is live, so it is additive on purpose:
 * a pick is matched on its player and updated in place, and nothing is ever
 * deleted. A sale typed into the room by hand survives the sync that follows
 * it, and ESPN's copy simply corrects the price when it arrives.
 */
class SyncEspnPicksAction
{
    private array $created = [];

    private array $updated = [];

    private array $skipped = [];

    /**
     * @return array<string, mixed>
     */
    public function run(Draft $draft): array
    {
        $league = $draft->league;

        $data = Espn::forcePull(true)->getFantasyDraftDetail(
            $league->credentials,
            $league->season_id,
        );

        $inProgress = (bool) Arr::get($data, 'in_progress');
        $isCompleted = (bool) Arr::get($data, 'is_completed');

        // ESPN publishes nothing through this view while a draft is running:
        // every slot comes back with a player and team of -1, and the picks
        // being made reach the app over the draft socket instead. Reading the
        // board now can only produce placeholders, so the poll waits until the
        // draft commits and the real picks appear.
        if ($inProgress && !$isCompleted) {
            return [
                'created'      => [],
                'updated'      => 0,
                'skipped'      => 0,
                'is_completed' => false,
                'in_progress'  => true,
            ];
        }

        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach (Arr::get($data, 'draftPicks', []) as $pick) {
            $this->syncPick($draft, $members->get($pick['league_member_id']), $pick);
        }

        return [
            'created'      => $this->created,
            'updated'      => count($this->updated),
            'skipped'      => count($this->skipped),
            'is_completed' => $isCompleted,
            'in_progress'  => $inProgress,
        ];
    }

    private function syncPick(Draft $draft, ?LeagueMember $member, array $pick): void
    {
        if (!$member instanceof LeagueMember) {
            Log::error('Member not found for draft pick', $pick);

            $this->skipped[] = $pick;

            return;
        }

        // ESPN's fantasy id is not an athlete id when the pick is a team
        // defense, so it is translated before it is looked up.
        $player = PlayerFacade::find(
            Espn::playerLookup($pick['player_id'], Arr::get($pick, 'full_name')),
            ['source' => static::class]
        );

        if (!$player instanceof Player) {
            // A dropped pick is a hole in the board the room is reading from,
            // so it is counted and shown rather than only logged.
            $this->skipped[] = $pick;

            Log::error('Player not found for draft pick', $pick);

            return;
        }

        $record = Action::model(DraftPick::class)->upsert([
            ...$pick,
            'draft_id'         => $draft->id,
            'league_member_id' => $member->id,
            'player_id'        => $player->id,
        ]);

        if ($record->wasRecentlyCreated) {
            $this->created[] = $record->id;

            return;
        }

        if ($record->wasChanged()) {
            $this->updated[] = $record->id;
        }
    }
}
