<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('espn:nfl:load:team-rosters', [
            '--all' => true,
            '--quiet' => true,
        ]);
    }
}
