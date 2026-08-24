<?php

namespace App\Actions\Models\Leagues;

use App\Enums\FantasyPlatforms;
use App\Facades\Action;
use App\Facades\Data;
use App\Models\Draft;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LeagueCreateAction
{
    public function run(User $creator, array $data): League
    {
        if (Str::upper(Arr::get($data, 'platform')) === FantasyPlatforms::ESPN->value) {
            return $this->createEspnLeague($creator, $data);
        }

        $league = League::create([
            'created_by_user_id' => $creator->id,
            'name'               => Arr::get($data, 'name'),
            'season'             => Arr::get($data, 'season', date('Y')),
            'slug'               => Str::slug(Arr::get($data, 'name')),
            'description'        => Arr::get($data, 'description'),
            'team_count'         => Arr::get($data, 'team_count'),
            'is_public'          => Arr::get($data, 'is_public'),
            'join_code'          => Str::upper(Str::random(8)),
            'is_active'          => true,
            'credentials'        => Arr::get($data, 'credentials'),
        ]);

        $this->createLeagueSettings($league, $data);

        $this->createLeagueMembers($league, $creator);

        $this->createDraft($league);

        return $league;
    }

    private function createEspnLeague(User $creator, array $data): League
    {
        return Data::espn()->importFantasyLeague([
            'created_by_user_id' => $creator->id,
            'league_id'          => Arr::get($data, 'credentials.leagueId'),
            's2'                 => Arr::get($data, 'credentials.s2'),
            'swid'               => Arr::get($data, 'credentials.swid'),
        ]);
    }

    private function createLeagueSettings(League $league, array $data): void
    {
        $settings = Arr::get($data, 'settings', []);

        Action::model(LeagueSettings::class)->create($league, [
            'roster_positions'            => Arr::get($settings, 'roster_positions', []),
            'roster_size'                 => Arr::get($settings, 'roster_size', 16),
            'starters_count'              => Arr::get($settings, 'starters_count', 9),
            'bench_count'                 => Arr::get($settings, 'bench_count', 7),
            'ir_spots'                    => Arr::get($settings, 'ir_spots', 1),
            'passing_points_per_yard'     => Arr::get($settings, 'passing_points_per_yard', 0.04),
            'passing_td_points'           => Arr::get($settings, 'passing_td_points', 4.0),
            'interception_points'         => Arr::get($settings, 'interception_points', -2.0),
            'rushing_points_per_yard'     => Arr::get($settings, 'rushing_points_per_yard', 0.1),
            'rushing_td_points'           => Arr::get($settings, 'rushing_td_points', 6.0),
            'receiving_points_per_yard'   => Arr::get($settings, 'receiving_points_per_yard', 0.1),
            'receiving_td_points'         => Arr::get($settings, 'receiving_td_points', 6.0),
            'reception_points'            => Arr::get($settings, 'reception_points', 0.0),
            'fumble_lost_points'          => Arr::get($settings, 'fumble_lost_points', -2.0),
            'two_point_conversion_points' => Arr::get($settings, 'two_point_conversion_points', 2.0),
        ]);
    }

    private function createLeagueMembers(League $league, User $user): void
    {
        for ($i = 0; $i < $league->team_count; $i++) {
            $userArg = ($i === 0) ? $user : null;
            Action::model(LeagueMember::class)->create($league, $userArg, [
                'team_name' => ($i === 0) ? $user->name . "'s Team" : 'Team ' . ($i + 1),
                'is_admin'  => $i === 0,
            ]);
        }
    }

    private function createDraft(League $league): void
    {
        Action::model(Draft::class)->create($league, [
            'draft_type' => 'snake',
            'draft_date' => null,
            'is_active'  => true,
        ]);
    }
}
