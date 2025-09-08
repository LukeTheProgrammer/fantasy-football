<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RosterSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('espn:roster:load-all');
    }
}
