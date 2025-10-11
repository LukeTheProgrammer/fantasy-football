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

    public function __construct(private League $league, private int $season)
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
        $this->rosters = Data::espn()->getFantasyLeagueRosters($this->league, $this->season);

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

    private function imporMembertRoster(Collection $roster, string $memberKey)
    {
        $memberId = str_replace('member.', '', $memberKey);

        $member = $this->league->members->firstWhere('id', $memberId);

        if (! $member instanceof LeagueMember) {
            throw new Exception('Member not found: ' . $memberId);
        }

        $roster->each(function ($weekRoster, $weekKey) use ($member) {
            $weekRoster = collect($weekRoster);

            if ($weekRoster->isEmpty()) {
                return true;
            }

            $weekNumber = (int) str_replace('week.', '', $weekKey);

            $this->importWeekRoster($weekRoster, $member, $weekNumber);
        });
    }

    private function importWeekRoster(Collection $roster, LeagueMember $member, int $week)
    {
        $roster->each(function ($player) use ($member, $week) {
            $this->importPlayer($member, $week, collect($player));
        });
    }

    private function importPlayer(LeagueMember $member, int $week, Collection $player)
    {
        $player['nfl_game_id'] = $this->getNflGameId($player, $week)?->id;
        $player['league_member_id'] = $member->id;
        $player['season'] = $this->season;
        $player['week'] = $week;
        $player['deleted_at'] = null;

        $find = Arr::only($player->toArray(), ['league_member_id', 'nfl_game_id', 'player_id', 'season', 'week']);

        $update = Arr::only($player->toArray(), [
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

    private function getNflGameId(Collection $player, int $week)
    {
        $teamId = Player::where('id', $player->get('player_id'))->select(['team_id']);

        return NflGame::query()
            ->where('season', $this->season)
            ->where('week', $week)
            ->where(function ($query) use ($teamId) {
                $query->orWhereIn('home_team_id', $teamId)
                    ->orWhereIn('away_team_id', $teamId);
            })
            ->first();
    }
}
