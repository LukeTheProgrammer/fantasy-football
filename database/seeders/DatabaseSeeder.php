<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => config('user.default.name'),
            'email' => config('user.default.email'),
            'password' => Hash::make(config('user.default.password')),
        ]);

        $path = database_path('data/teams.json');
        $json = file_get_contents($path);
        $teams = json_decode($json, true);

        foreach ($teams as $team) {
            Team::upsert([
                'espn_id'       => Arr::get($team, 'espn_id'),
                'abbreviation'  => Arr::get($team, 'abbreviation'),
                'location'      => Arr::get($team, 'location'),
                'name'          => Arr::get($team, 'name'),
                'conference'    => Arr::get($team, 'conference'),
                'division'      => Arr::get($team, 'division'),
                'logo'          => Arr::get($team, 'logo'),
            ], ['espn_id']);
        }
    }
}
