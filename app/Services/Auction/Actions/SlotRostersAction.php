<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;

/**
 * Places each team's picks into the roster the league is configured for.
 *
 * Slots are filled in the order the league lists them, and a player takes the
 * first slot he is eligible for: his own position before the flex, the flex
 * before the bench. Picks are slotted most expensive first, since in an auction
 * the price is the clearest signal of who was bought to start.
 */
class SlotRostersAction
{
    /**
     * Slots a player can never start in, whatever his position.
     */
    public const RESERVE_SLOTS = ['BE', 'IR'];

    /**
     * @return Collection<int, array> Roster slots keyed by league member id.
     */
    public function run(Draft $draft): Collection
    {
        $template = $draft->league->settings?->roster_positions ?? [];

        $picks = $draft->picks
            ->sortByDesc('amount')
            ->groupBy('league_member_id');

        return $draft->league->members
            ->mapWithKeys(fn (LeagueMember $member) => [
                $member->id => $this->slots($template, $picks->get($member->id) ?? collect()),
            ]);
    }

    /**
     * Fill one team's roster template.
     *
     * @param array<int, string> $template
     * @param Collection<int, DraftPick> $picks
     *
     * @return array<int, array>
     */
    private function slots(array $template, Collection $picks): array
    {
        $slots = collect($template)
            ->map(fn (string $slot, int $index) => [
                'index'      => $index,
                'slot'       => $slot,
                'label'      => $this->label($slot),
                'is_starter' => !in_array($slot, self::RESERVE_SLOTS),
                'player'     => null,
            ])
            ->all();

        foreach ($picks as $pick) {
            $position = $pick->player?->position_id;

            $target = $this->firstOpenSlot($slots, $position);

            if ($target === null) {
                // Nowhere left to put him: a roster with more picks than slots
                // still shows every pick rather than dropping one silently.
                $slots[] = [
                    'index'      => count($slots),
                    'slot'       => 'OVER',
                    'label'      => 'Extra',
                    'is_starter' => false,
                    'player'     => null,
                ];

                $target = array_key_last($slots);
            }

            $slots[$target]['player'] = [
                'player_id'   => $pick->player_id,
                'pick_id'     => $pick->id,
                'full_name'   => $pick->player?->full_name,
                'position_id' => $position,
                'team_id'     => $pick->player?->team_id,
                'amount'      => (int) $pick->amount,
            ];
        }

        return array_values($slots);
    }

    /**
     * The first slot this player can fill, preferring his own position, then
     * the flex, then the bench.
     *
     * @param array<int, array> $slots
     */
    private function firstOpenSlot(array $slots, ?string $position): ?int
    {
        if (empty($position)) {
            return $this->firstOpen($slots, fn ($slot) => in_array($slot['slot'], self::RESERVE_SLOTS));
        }

        return $this->firstOpen($slots, fn ($slot) => $slot['slot'] === $position)
            ?? $this->firstOpen($slots, fn ($slot) => $this->isFlexFor($slot['slot'], $position))
            ?? $this->firstOpen($slots, fn ($slot) => $slot['slot'] === 'BE')
            ?? $this->firstOpen($slots, fn ($slot) => $slot['slot'] === 'IR');
    }

    /**
     * @param array<int, array> $slots
     */
    private function firstOpen(array $slots, callable $matches): ?int
    {
        foreach ($slots as $index => $slot) {
            if ($slot['player'] === null && $matches($slot)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Flex slots name the positions they accept, joined by underscores.
     */
    private function isFlexFor(string $slot, string $position): bool
    {
        return str_contains($slot, '_') && in_array($position, explode('_', $slot));
    }

    /**
     * Flex slots read better as the positions they take.
     */
    private function label(string $slot): string
    {
        return str_contains($slot, '_') ? str_replace('_', '/', $slot) : $slot;
    }
}
