<?php

namespace App\Services\Auction\Actions;

use App\Facades\Action;
use App\Facades\Espn;
use App\Facades\Player as PlayerFacade;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\LeagueMember;
use App\Models\Player;
use Illuminate\Support\Facades\Log;

/**
 * Write one sale from the draft socket onto the board.
 *
 * The socket is the only live source of picks — ESPN's REST views publish
 * nothing until a draft commits — so this is what fills the board while a
 * draft runs. It writes through the same upsert the ESPN sync uses, so a sale
 * that arrives twice, or that the post-draft sync later confirms, lands on the
 * same row rather than a second one.
 */
class RecordSoldPickAction
{
    /**
     * @param array<string, int|float> $sold
     */
    public function run(Draft $draft, array $sold): ?DraftPick
    {
        $member = LeagueMember::forLeague($draft->league)
            ->where('external_id', $sold['espn_team_id'])
            ->first();

        if (!$member instanceof LeagueMember) {
            Log::error('Member not found for sold frame', $sold);

            return null;
        }

        // A team defense is not an athlete id at ESPN, so the id is translated
        // before it is looked up.
        $player = PlayerFacade::find(
            Espn::playerLookup($sold['player_id']),
            ['source' => static::class]
        );

        if (!$player instanceof Player) {
            Log::error('Player not found for sold frame', $sold);

            return null;
        }

        return Action::model(DraftPick::class)->upsert([
            'draft_id'         => $draft->id,
            'league_member_id' => $member->id,
            'player_id'        => $player->id,
            'amount'           => $sold['amount'],
            'round'            => 0,
            ...$this->numbering($draft, $player),
        ]);
    }

    /**
     * Where this sale sits on the board.
     *
     * A SOLD frame says nothing about when it happened — its third field is
     * the roster slot ESPN gave the player, which repeats across teams — but
     * the table's unique key is (draft, round, pick_number), so every sale
     * still needs a number of its own. Frames arrive in the order they were
     * sold, so the board counts them itself. A player already on the board
     * keeps the number he was given, which is what makes a repeated frame an
     * update rather than a second pick.
     *
     * @return array<string, int>
     */
    private function numbering(Draft $draft, Player $player): array
    {
        $existing = DraftPick::where('draft_id', $draft->id)
            ->where('player_id', $player->id)
            ->value('overall_pick_number');

        $number = $existing ?: (int) DraftPick::where('draft_id', $draft->id)
            ->max('overall_pick_number') + 1;

        return [
            'pick_number'         => $number,
            'overall_pick_number' => $number,
        ];
    }
}
