<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::upsert([
            'name'     => config('user.default.name'),
            'email'    => config('user.default.email'),
            'password' => Hash::make(config('user.default.password')),
        ], ['email']);

        $this->call([
            PositionSeeder::class,
            TeamSeeder::class,
            RosterSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
