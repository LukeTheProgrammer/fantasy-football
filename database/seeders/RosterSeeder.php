<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RosterSeeder extends Seeder
{
    public function run(): void
    {
        $this->espn();
        // $this->pfr();
    }

    public function espn()
    {
        Artisan::call('scrapers:espn:get-roster', [
            '--all' => true,
            '--quiet' => true,
        ]);
    }

    public function pfr()
    {
        Artisan::call('scrapers:pfr:get-roster', [
            '--all' => true,
            'year'  => 2025,
        ]);

        Artisan::call('scrapers:pfr:get-roster', [
            '--all' => true,
            'year'  => 2024,
        ]);
    }
}
