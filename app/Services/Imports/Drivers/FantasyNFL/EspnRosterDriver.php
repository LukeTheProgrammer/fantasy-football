<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Enums\Datum;
use App\Facades\Data;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerProjection;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EspnRosterDriver
{
    private array|Collection|null $rosters;

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
        $this->rosters = Data::forcePull(false)
            ->dataFormat(Datum::FORMAT_FORMATTED)
            ->espn()
            ->getFantasyLeagueRosters($this->league, $this->year);

        if (! $this->rosters instanceof Collection) {
            $this->rosters = collect($this->rosters);
        }

        DB::table('league_member_rosters')
            ->whereIn('league_member_id', $this->league->members->pluck('id')->toArray())
            ->update(['deleted_at' => now()]);
    }

    private function importRosters()
    {
        $this->rosters->each(function ($roster, $memberKey) {
            $this->imporMembertRoster($roster, $memberKey);
        });
    }

    private function imporMembertRoster(array|Collection $roster, string $memberKey)
    {
        $roster = (! $roster instanceof Collection) ? collect($roster) : $roster;

        $memberId = str_replace('member.', '', $memberKey);

        // dd([$memberKey, $memberId, $roster]);

        $member = $this->league->members->firstWhere('id', $memberId);

        if (! $member instanceof LeagueMember) {
            throw new Exception('Member not found: ' . $memberId);
        }

        $roster->each(function ($weekRoster, $weekKey) use ($member) {
            $weekNumber = (int) str_replace('week.', '', $weekKey);

            $this->importWeekRoster($weekRoster, $member, $weekNumber);
        });
    }

    private function importWeekRoster(array|Collection $roster, LeagueMember $member, int $week)
    {
        $roster = (! $roster instanceof Collection) ? collect($roster) : $roster;

        $roster->each(function ($player) use ($member, $week) {
            $this->importPlayer($member, $week, $player);
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
