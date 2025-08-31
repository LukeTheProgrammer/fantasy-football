<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    protected ?int $dst = null;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::upsert([
            'name'  => config('user.default.name'),
            'email' => config('user.default.email'),
            'password' => Hash::make(config('user.default.password')),
        ], ['email']);

        $this->loadPositions();
        $this->loadTeams();
    }

    protected function loadPositions()
    {
        $path = database_path('data/positions.json');
        $json = file_get_contents($path);
        $positions = json_decode($json, true);

        foreach ($positions as $position) {
            $pos = Position::updateOrCreate(
                [ 'abbreviation' => Arr::get($position, 'abbreviation') ],
                [ 'name'         => Arr::get($position, 'name') ]
            );

            if ($pos->abbreviation === 'DST') {
                $this->dst = $pos->id;
            }
        }
    }

    protected function loadTeams()
    {
        $path = database_path('data/teams.json');
        $json = file_get_contents($path);
        $teams = json_decode($json, true);

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
                    'position_id' => $this->dst,
                    'team_id' => $team->id,
                    'first_name' => Arr::get($team, 'location'),
                    'last_name' => Arr::get($team, 'name'),
                    'full_name' => Arr::get($team, 'location') . ' ' . Arr::get($team, 'name'),
                ]
            );
        }
    }
}
