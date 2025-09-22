<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('espn:nfl:get:team-schedule', [
            '--all' => true,
            '--quiet' => true,
            'year' => 2024,
        ]);

        Artisan::call('espn:nfl:get:team-schedule', [
            '--all' => true,
            '--quiet' => true,
            'year' => 2025,
        ]);

        Artisan::call('espn:nfl:load:team-schedules', [
            '--all' => true,
            '--quiet' => true,
            'year' => 2024,
        ]);

        Artisan::call('espn:nfl:load:team-schedules', [
            '--all' => true,
            '--quiet' => true,
            'year' => 2025,
        ]);
    }
}
