<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('import:nfl:schedule', [
            'season' => 2024,
        ]);

        Artisan::call('import:nfl:schedule', [
            'season' => 2025,
        ]);

        Artisan::call('import:nfl:schedule', [
            'season' => 2026,
        ]);
    }
}
