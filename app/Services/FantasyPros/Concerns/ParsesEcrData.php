<?php

namespace App\Services\FantasyPros\Concerns;

use Illuminate\Support\Arr;

/**
 * Every FantasyPros ranking page embeds its rows in the same `ecrData` object,
 * so both the positional slates and the overall draft boards are read the same
 * way.
 */
trait ParsesEcrData
{
    /**
     * Extract the players array from the ecrData JSON inside a page.
     *
     * Falls back to extracting only the players array when the surrounding
     * object will not parse.
     */
    protected function parseEcrData(string $html): array
    {
        // Primary: capture the full ecrData object
        if (preg_match('/var\s+ecrData\s*=\s*(\{.*?\});/s', $html, $m)) {
            $json = rtrim($m[1] ?? '');
            $json = preg_replace('/;\s*$/', '', $json);

            $data = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $players = Arr::get($data, 'players');

                if (is_array($players)) {
                    return $this->filterEcrPlayers($players);
                }
            }
        }

        // Fallback: capture only the players array contents and decode
        if (preg_match('/"players"\s*:\s*\[(.*?)\]/s', $html, $m2)) {
            $players = json_decode('[' . ($m2[1] ?? '') . ']', true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($players)) {
                return $this->filterEcrPlayers($players);
            }
        }

        return [];
    }

    /**
     * Keep only rows carrying the identifiers an import needs.
     */
    protected function filterEcrPlayers(array $players): array
    {
        return array_values(array_filter($players, function ($player) {
            return !empty(Arr::get($player, 'player_id'))
                && !empty(Arr::get($player, 'player_name'))
                && !empty(Arr::get($player, 'player_position_id'))
                && !empty(Arr::get($player, 'player_team_id'));
        }));
    }
}
