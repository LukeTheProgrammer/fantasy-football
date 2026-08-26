<?php

namespace App\Services\Espn\Helpers;

use App\Enums\NFLPositions;

/**
 * Translates an ESPN fantasy player id into something this app can look up.
 *
 * Team defenses are not players to ESPN, they are teams, and it gives them a
 * negative id derived from the team's own: the Packers, team 9, are player
 * -16009. The app stores a defense as a player row carrying the team's id, so
 * the two only meet if the sign and the offset are undone first.
 *
 * This lived inline in the roster formatter and nowhere else, which is why
 * every drafted defense was silently dropped from the draft.
 */
class FantasyPlayerId
{
    /**
     * The offset ESPN's negative defense ids are built from.
     */
    public const DEFENSE_OFFSET = 16000;

    public function isDefense(int|string $playerId): bool
    {
        return (int) $playerId < 0;
    }

    /**
     * The espn id the app stores for this player, which for a defense is the
     * team's.
     */
    public function espnId(int|string $playerId): int
    {
        $playerId = (int) $playerId;

        return $this->isDefense($playerId)
            ? abs($playerId + self::DEFENSE_OFFSET)
            : $playerId;
    }

    /**
     * The data a player lookup needs to resolve this id.
     *
     * @return array<string, mixed>
     */
    public function lookup(int|string $playerId, ?string $name = null): array
    {
        $data = [
            'espn_id'   => $this->espnId($playerId),
            'full_name' => $name,
        ];

        if ($this->isDefense($playerId)) {
            // A team id is a small number that could collide with a real
            // athlete's id, so a defense says so rather than trusting the id.
            $data['position_id'] = NFLPositions::DST->value;
        }

        return $data;
    }
}
