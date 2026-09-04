<?php

namespace App\Services\Rosters\Actions;

use Illuminate\Support\Collection;

/**
 * Fills a league's roster template with an ordered squad.
 *
 * A player takes the first slot he is eligible for: his own position before
 * the flex, the flex before the bench. The order the squad arrives in is what
 * decides who gets the better slot, and that order is the caller's to choose —
 * an auction reads it off the price, a pick draft off the rankings.
 */
class SlotLineupAction
{
    /**
     * Slots a player can never start in, whatever his position.
     */
    public const RESERVE_SLOTS = ['BE', 'IR'];

    /**
     * @param array<int, string> $template Roster slots, in the order the league lists them.
     * @param Collection<int, array<string, mixed>> $squad Entries of ['position' => ?string, 'player' => mixed], best first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function run(array $template, Collection $squad): array
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

        foreach ($squad as $entry) {
            $target = $this->firstOpenSlot($slots, $entry['position'] ?? null);

            if ($target === null) {
                // A squad bigger than the template still shows every player
                // rather than dropping one silently.
                $slots[] = [
                    'index'      => count($slots),
                    'slot'       => 'OVER',
                    'label'      => 'Extra',
                    'is_starter' => false,
                    'player'     => null,
                ];

                $target = array_key_last($slots);
            }

            $slots[$target]['player'] = $entry['player'];
        }

        return array_values($slots);
    }

    /**
     * The first slot this player can fill, preferring his own position, then
     * the flex, then the bench.
     *
     * @param array<int, array<string, mixed>> $slots
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
     * @param array<int, array<string, mixed>> $slots
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
