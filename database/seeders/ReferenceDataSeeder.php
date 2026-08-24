<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The fixed lists the app is built on: positions and NFL teams.
 *
 * These never vary between environments, so tests seed them rather than
 * generating them. Everything else a test needs is genuinely variable and
 * belongs in a factory.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            TeamSeeder::class,
        ]);
    }
}
