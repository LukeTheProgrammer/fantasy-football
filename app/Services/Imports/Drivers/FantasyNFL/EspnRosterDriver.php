<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\Player;
use App\Models\NflGame;
use App\Services\Espn\Resources\FantasyNFL;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Data\FantasyNFL\PlayerData;
use App\Services\Espn\Formatters\FantasyNFLRosterFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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

        /** @var TeamRosterData $rosterData */
        $rosterData = $leagueData->teams->first()->roster;

        $rosterData->entries->each(function (TeamRosterEntryData $player) use ($member, $week) {
            $this->importPlayer($member, $week, $player);
        });
    }

    private function importPlayer(LeagueMember $member, int $week, TeamRosterEntryData $rosterEntry)
    {
        $data = FantasyNFLRosterFormatter::formatRosterEntry($rosterEntry, $this->year, $week);

        if (null === $data) {
            return;
        }

        $data['league_member_id'] = $member->id;
        $data['season'] = $this->year;
        $data['week'] = $week;
        $data['deleted_at'] = null;

        LeagueMemberRoster::query()->withTrashed()->updateOrCreate(
            Arr::only($data, ['league_member_id', 'nfl_game_id', 'player_id', 'season', 'week']),
            Arr::except($data, ['league_member_id', 'nfl_game_id', 'player_id', 'season', 'week'])
        );
    }
}
