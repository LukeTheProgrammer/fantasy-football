<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ProjectionsSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('import:nfl:projections', [
            'season' => 2026,
            'week' => 1,
        ]);
    }
}
