<?php

namespace App\Services\CBS\Formatters;

use App\Services\CBS\CBSConstants;
use Illuminate\Support\Arr;

/**
 * Turns the CBS draft results payload into the shape the pick sync writes.
 *
 * CBS publishes every slot of the draft, made or not. A slot nobody has reached
 * yet carries the literal player id "UpcomingPick"; those are the order rather
 * than the result, and are dropped here because the order is already recorded
 * on the draft. A slot the team gave up carries the id "0" and no player at
 * all, and that is kept: the clock reads the first slot with nothing against
 * it, so a pass the app never hears about strands the room on a pick the real
 * draft has already moved past.
 */
class DraftPicksFormatter
{
    /**
     * The id CBS gives a slot nobody has picked in yet.
     */
    public const UPCOMING = 'UpcomingPick';

    /**
     * The id CBS gives a slot whose team took nobody with it.
     */
    public const PASSED = '0';

    public function __construct(private array $payload)
    {
        //
    }

    /**
     * @return array<string, mixed>
     */
    public static function from(array $payload): array
    {
        return (new self($payload))->format();
    }

    /**
     * @return array<string, mixed>
     */
    public function format(): array
    {
        $slots = Arr::get($this->payload, 'draft_results.picks', []);

        $picks = [];

        foreach ($slots as $slot) {
            $id = (string) Arr::get($slot, 'player.id', '');

            // Anything without an id of its own is a slot the draft has not
            // reached, whatever CBS chose to call it that day.
            if ($id === '' || $id === self::UPCOMING) {
                continue;
            }

            $picks[] = $this->formatPick($slot);
        }

        $state = Arr::get($this->payload, 'draft_results.state');

        return [
            'picks' => $picks,
            'state' => $state,
            // CBS reports the draft as complete by its state rather than by the
            // count, which is what a draft cut short by the commissioner needs.
            'is_completed' => $state === 'complete',
            'total_slots'  => count($slots),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPick(array $slot): array
    {
        $player = Arr::get($slot, 'player', []);
        $passed = (string) Arr::get($player, 'id') === self::PASSED;

        return [
            // A slot the team gave up is a used slot with nobody in it.
            'is_passed' => $passed,
            // The team's own id at CBS, which is what a member is keyed on.
            'league_member_id'    => (string) Arr::get($slot, 'team.id'),
            'overall_pick_number' => (int) Arr::get($slot, 'overall_pick'),
            'round'               => (int) Arr::get($slot, 'round'),
            'pick_number'         => (int) Arr::get($slot, 'round_pick'),
            'is_keeper'           => (bool) Arr::get($slot, 'is_keeper'),
            // A pick draft spends a slot rather than money.
            'amount' => null,
            // CBS names a player in two halves and has no id this app stores,
            // so he is resolved on his name, position and pro team.
            'full_name'   => trim(Arr::get($player, 'firstname', '') . ' ' . Arr::get($player, 'lastname', '')),
            'position_id' => $this->positionId(Arr::get($player, 'position')),
            'team_id'     => Arr::get($player, 'pro_team'),
        ];
    }

    /**
     * A position CBS did not name is no position.
     *
     * Read with a null key, Arr::get answers with the whole map rather than
     * with nothing, so the empty case is turned away before the lookup.
     */
    private function positionId(?string $position): ?string
    {
        if (empty($position)) {
            return null;
        }

        return Arr::get(CBSConstants::POSITION_MAP, $position)?->value;
    }
}
