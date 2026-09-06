<?php

namespace App\Services\Picks\Actions;

use App\Facades\Action;
use App\Facades\CBS;
use App\Facades\Player as PlayerFacade;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\LeagueMember;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Pull the picks CBS has recorded for a draft and write the new ones in.
 *
 * Additive on purpose, the same as the ESPN sync: a pick is matched on its
 * player and updated in place, and nothing is ever deleted. A pick typed into
 * the room by hand survives the sync that follows it, and a bonus pick the
 * commissioner awarded off-platform — which CBS will never publish — is not
 * quietly removed because the feed has no row for it.
 */
class SyncCbsPicksAction
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

        $data = CBS::forcePull(true)->getFantasyDraftPicks($league->credentials);

        $members = LeagueMember::forLeague($league)->get()->keyBy('external_id');

        foreach (Arr::get($data, 'picks', []) as $pick) {
            $this->syncPick($draft, $members->get($pick['league_member_id']), $pick);
        }

        return [
            'created'      => $this->created,
            'updated'      => count($this->updated),
            'skipped'      => count($this->skipped),
            'is_completed' => (bool) Arr::get($data, 'is_completed'),
            // CBS says "picking" while the room is live and "complete" when it
            // is over, which is the only progress signal the feed carries.
            'in_progress' => Arr::get($data, 'state') === 'picking',
        ];
    }

    private function syncPick(Draft $draft, ?LeagueMember $member, array $pick): void
    {
        if (!$member instanceof LeagueMember) {
            Log::error('Member not found for CBS draft pick', $pick);

            $this->skipped[] = $pick;

            return;
        }

        // A slot its team gave up still spends the slot, so it is written with
        // nobody in it rather than looked up and reported as a miss.
        if (Arr::get($pick, 'is_passed')) {
            $this->write($draft, $member, $pick, null);

            return;
        }

        // CBS names a team defense by its nickname, which is not the name the
        // app stores it under, so the lookup is translated before it is made.
        $player = PlayerFacade::find(CBS::playerLookup([
            'full_name'   => $pick['full_name'],
            'position_id' => $pick['position_id'],
            'team_id'     => $pick['team_id'],
        ]), ['source' => static::class]);

        if (!$player instanceof Player) {
            // A dropped pick is a hole in the board the room is reading from,
            // so it is counted and shown rather than only logged.
            $this->skipped[] = $pick;

            Log::error('Player not found for CBS draft pick', $pick);

            return;
        }

        $this->write($draft, $member, $pick, $player);
    }

    private function write(Draft $draft, LeagueMember $member, array $pick, ?Player $player): void
    {
        $record = Action::model(DraftPick::class)->upsert([
            ...Arr::except($pick, ['full_name', 'position_id', 'team_id', 'is_passed']),
            'draft_id'         => $draft->id,
            'league_member_id' => $member->id,
            'player_id'        => $player?->id,
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
