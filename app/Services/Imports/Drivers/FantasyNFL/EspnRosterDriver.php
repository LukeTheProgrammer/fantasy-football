<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerProjection;
use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Resources\FantasyNFL;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Formatters\FantasyNFLRosterFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EspnRosterDriver
{
    private FantasyNFL $espn;

    public function __construct(private League $league, private int $year)
    {
        //
    }

    public function import(): League
    {
        $this->setUp();

        $this->importRosters();

        return $this->league;
    }

    //

    private function setUp()
    {
        $this->espn = Espn::fantasyNFL($this->league->credentials);

        DB::table('league_member_rosters')
            ->whereIn('league_member_id', $this->league->members->pluck('id')->toArray())
            ->update(['deleted_at' => now()]);
    }

    private function importRosters()
    {
        $this->league->members->each(function ($member) {
            for ($week = 1; $week <= 18; $week++) {
                $this->importRoster($member, $week);
            }
        });
    }

    private function importRoster(LeagueMember $member, int $week)
    {
        /** @var ResourceLeagueData $leagueData */
        $leagueData = $this->espn->getRostersForTeam($member->external_id, $week, $this->year);

        $formatter = new FantasyNFLRosterFormatter($leagueData, $this->year, $week);

        $roster = $formatter->getFormattedRoster();

        $roster->each(function ($team) use ($member, $week) {
            $team->each(function ($player) use ($member, $week) {
                $this->importPlayer($member, $week, $player);
            });
        });
    }

    private function importPlayer(LeagueMember $member, int $week, array|Collection $player)
    {
        $player = ($player instanceof Collection) ? $player->toArray() : $player;

        $player['nfl_game_id'] = $this->getNflGameId($player, $week)?->id;
        $player['league_member_id'] = $member->id;
        $player['season'] = $this->year;
        $player['week'] = $week;
        $player['deleted_at'] = null;

        $find = Arr::only($player, ['league_member_id', 'nfl_game_id', 'player_id', 'season', 'week']);

        $update = Arr::only($player, [
            'lineup_slot_id',
            'position_rank',
            'overall_rank',
            'percent_owned',
            'percent_started',
            'percent_changed',
            'fantasy_points',
            'deleted_at',
        ]);

        LeagueMemberRoster::query()->withTrashed()->updateOrCreate($find, $update);

        $proj = Arr::get($player, 'espn_projected_points');

        if ($proj && $proj > 0) {
            PlayerProjection::updateOrCreate([
                'player_id' => $player['player_id'],
                'season'    => $player['season'],
                'week'      => $player['week'],
            ], [
                'espn_projected_points' => $proj,
            ]);
        }
    }

    private function getNflGameId(array $player, int $week)
    {
        $teamId = Player::where('id', $player['player_id'])->select(['team_id']);

        return NflGame::query()
            ->where('year', $this->year)
            ->where('week', $week)
            ->where(function ($query) use ($teamId) {
                $query->orWhereIn('home_team_id', $teamId)
                    ->orWhereIn('away_team_id', $teamId);
            })
            ->first();
    }
}
