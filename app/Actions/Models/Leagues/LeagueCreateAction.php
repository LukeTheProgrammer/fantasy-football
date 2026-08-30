<?php

namespace App\Actions\Models\Leagues;

use App\Enums\FantasyPlatforms;
use App\Facades\Action;
use App\Facades\Data;
use App\Models\Draft;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\Season;
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

        $season = Arr::get($data, 'season', date('Y'));

        // leagues.season_id is a key into seasons, so the year has to exist
        // there before a league can claim it.
        Season::firstOrCreate(['id' => $season], ['is_current' => false]);

        $league = League::create([
            'created_by_user_id' => $creator->id,
            'name'               => Arr::get($data, 'name'),
            'season_id'          => $season,
            'slug'               => Str::slug(Arr::get($data, 'name')),
            'description'        => Arr::get($data, 'description'),
            'team_count'         => Arr::get($data, 'team_count'),
            'is_public'          => Arr::get($data, 'is_public'),
            'platform_id'        => Arr::get($data, 'platform_id'),
            'join_code'          => Str::upper(Str::random(8)),
            'is_active'          => true,
            'credentials'        => Arr::get($data, 'credentials'),
        ]);

        $this->createLeagueSettings($league, $data);

        $this->createLeagueMembers($league, $creator, $data);

        $this->createDraft($league, $data);

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
            'ppr'                         => Arr::get($settings, 'ppr', 'standard'),
            'two_qb'                      => Arr::get($settings, 'two_qb', false),
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

    /**
     * The league's teams. A caller that already knows them -- an import, or a
     * league mirrored from a platform -- passes them under 'members'; anyone
     * else gets numbered placeholders. The creator takes the first seat either
     * way, since that is the seat the app is looked at from.
     */
    private function createLeagueMembers(League $league, User $user, array $data): void
    {
        $members = Arr::get($data, 'members', []);

        for ($i = 0; $i < $league->team_count; $i++) {
            $userArg = ($i === 0) ? $user : null;

            $member = Arr::get($members, $i, []);

            Action::model(LeagueMember::class)->create($league, $userArg, [
                'team_name'   => Arr::get($member, 'team_name', ($i === 0) ? $user->name . "'s Team" : 'Team ' . ($i + 1)),
                'owner_name'  => Arr::get($member, 'owner_name', ($i === 0) ? $user->name : null),
                'external_id' => Arr::get($member, 'external_id'),
                'is_admin'    => $i === 0,
            ]);
        }
    }

    private function createDraft(League $league, array $data): void
    {
        $draft = Arr::get($data, 'draft', []);

        Action::model(Draft::class)->create($league, [
            'draft_type'     => Arr::get($draft, 'draft_type', 'snake'),
            'draft_date'     => Arr::get($draft, 'draft_date'),
            'auction_budget' => Arr::get($draft, 'auction_budget'),
            'is_active'      => Arr::get($draft, 'is_active', true),
        ]);
    }
}
