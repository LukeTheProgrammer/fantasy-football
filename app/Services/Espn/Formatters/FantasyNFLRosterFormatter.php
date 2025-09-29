<?php

namespace App\Services\Espn\Formatters;

use App\Models\Player;
use App\Models\NflGame;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Data\FantasyNFL\PlayerStatsData;
use Illuminate\Support\Arr;

class FantasyNFLRosterFormatter
{
    public static function fromLeague(ResourceLeagueData $leagueData, int $season, int $week)
    {
        return $leagueData->teams->map(
            fn (TeamRosterEntryData $team) => self::formatRosterEntry($team, $season, $week)
        )->filter();
    }

    public static function formatRosterEntry(TeamRosterEntryData $entry, int $season, int $week)
    {
        $player = $entry->playerPoolEntry->player;
        $ratings = $entry->playerPoolEntry->ratings;
        $stats = $player->stats;

        $playerId = $player->id;

        if ($playerId < 0) {
            // ESPN uses negative numbers for DSTs.
            $playerId += 16000;
            $playerId = abs($playerId);
        }

        $playerModel = Player::espnId($playerId)->first();

        if (! $playerModel instanceof Player) {
            dump('Player Not Found ' . json_encode([
                'id' => $player->id,
                'playerId' => $playerId,
                'name' => $player->fullName,
            ]));

            return null;
        }

        $nflGame = NflGame::forTeam($playerModel->team_id)
            ->forYear($season)
            ->forWeek($week)
            ->first();

        $data = [
            'player_id'             => $playerModel->id,
            'nfl_game_id'           => $nflGame?->id,
            'season'                => $season,
            'week'                  => $week,
            'fantasy_points'        => 0,
            'lineup_slot_id'        => $entry->lineupSlotId,
            'position_rank'         => $ratings->first()->positionalRanking,
            'overall_rank'          => $ratings->first()->totalRanking,
            'percent_owned'         => $player->ownership->percentOwned,
            'percent_started'       => $player->ownership->percentStarted,
            'percent_changed'       => $player->ownership->percentChange,
        ];

        foreach ($stats as $stat) {
            $data = self::processStat($stat, $data);
        }

        return $data;
    }

    public static function processStat(PlayerStatsData $stat, array $data = [])
    {
        $season = Arr::get($stat, 'season');
        $week = Arr::get($stat, 'week');

        $gameId = intval($stat->externalId);
        $statWeek = intval($stat->scoringPeriodId);
        $points = floatVal($stat->appliedTotal);

        $isWeek = $week == $statWeek;
        $isProjection = $stat->statSourceId === 1;
        $projectionKey = $season . $week;

        if ($isWeek && $gameId == $projectionKey && $isProjection) {
            $data['espn_projected_points'] = $points;
        }

        return $data;
    }
}
