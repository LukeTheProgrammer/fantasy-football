<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/teams.json');
        $json = file_get_contents($path);
        $teams = json_decode($json, true);
        $dst = Position::firstOrCreate(['abbreviation' => 'DST'], ['name' => 'Defense']);

        foreach ($teams as $team) {
            $team = Team::updateOrCreate(
                ['espn_id' => Arr::get($team, 'espn_id')],
                [
                    'abbreviation'  => Arr::get($team, 'abbreviation'),
                    'location'      => Arr::get($team, 'location'),
                    'name'          => Arr::get($team, 'name'),
                    'conference'    => Arr::get($team, 'conference'),
                    'division'      => Arr::get($team, 'division'),
                    'logo'          => Arr::get($team, 'logo'),
                ]
            );

            // Create all the DST players
            Player::updateOrCreate(
                ['espn_id' => Arr::get($team, 'espn_id')],
                [
                    'position_id' => $dst->id,
                    'team_id'     => $team->id,
                    'first_name'  => Arr::get($team, 'location'),
                    'last_name'   => Arr::get($team, 'name'),
                    'full_name'   => Arr::get($team, 'location') . ' ' . Arr::get($team, 'name'),
                    'headshot'    => Arr::get($team, 'logo'),
                ]
            );
        }
    }
}
