<?php

namespace App\Services\Espn\Formatters;

use App\Models\Player;
use App\Models\NflGame;
use App\Services\Espn\Data\FantasyNFL\PlayerStatsData;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class FantasyNFLRosterFormatter
{
    protected array $nflGameIds = [];

    public function __construct(
        protected ResourceLeagueData $leagueData,
        protected int $season,
        protected int $week
    ) {
        $this->nflGameIds = NflGame::select(['id', 'espn_id'])
            ->forYear($season)
            ->forWeek($week)
            ->get()
            ->mapWithKeys(fn ($g) => [$g->espn_id => $g->id])
            ->toArray();
    }

    public function getFormattedRoster()
    {
        return $this->leagueData->teams->map(
            fn (ResourceTeamsData $team) => $this->formatTeamRoster($team)
        )->filter();
    }

    public function formatTeamRoster(ResourceTeamsData $team)
    {
        return $team->roster->entries->map(
            fn (TeamRosterEntryData $entry) => $this->formatRosterEntry($entry)
        )->filter();
    }

    public function formatRosterEntry(TeamRosterEntryData $entry)
    {
        $player = $entry->playerPoolEntry->player;
        $ratings = $entry->playerPoolEntry->ratings;
        $stats = $player->stats;

        $playerId = $player->id;

        if ($playerId < 0) {
            // ESPN uses negative numbers for DSTs.
            $playerId = abs($playerId + 16000);
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

        $data = [
            'player_id'             => $playerModel->id,
            'nfl_game_id'           => null,
            'season'                => $this->season,
            'week'                  => $this->week,
            'fantasy_points'        => 0,
            'espn_projected_points' => 0,
            'lineup_slot_id'        => $entry->lineupSlotId,
            'position_rank'         => $ratings->first()->positionalRanking,
            'overall_rank'          => $ratings->first()->totalRanking,
            'percent_owned'         => $player->ownership->percentOwned,
            'percent_started'       => $player->ownership->percentStarted,
            'percent_changed'       => $player->ownership->percentChange,
        ];

        foreach ($stats as $stat) {
            $data = $this->processStat($stat, $data);
        }

        return $data;
    }

    public function processStat(PlayerStatsData $stat, array $data = [])
    {
        $gameId = intval($stat->externalId);
        $season = intval($stat->seasonId);
        $week   = intval($stat->scoringPeriodId);
        $points = floatVal($stat->appliedTotal);

        $isWeek = $week == $this->week;
        $isProjection = $stat->statSourceId === 1;
        $projectionKey = $season . $week;

        if ($isWeek && $gameId == $projectionKey && $isProjection) {
            $data['espn_projected_points'] = $points;
        }

        if ($isWeek && isset($this->nflGameIds[$gameId])) {
            $data['fantasy_points'] = $points;
            $data['nfl_game_id'] = $this->nflGameIds[$gameId];
        }

        return $data;
    }
}
