<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create all positions first
        $qbPosition = Position::factory()->quarterback()->create();
        $rbPosition = Position::factory()->runningBack()->create();
        $wrPosition = Position::factory()->wideReceiver()->create();
        $tePosition = Position::factory()->tightEnd()->create();
        $kPosition = Position::factory()->kicker()->create();
        $dstPosition = Position::factory()->defense()->create();

        // Create teams for each division
        Team::factory()->afcEast()->count(4)->create();
        Team::factory()->afcNorth()->count(4)->create();
        Team::factory()->afcSouth()->count(4)->create();
        Team::factory()->afcWest()->count(4)->create();
        Team::factory()->nfcEast()->count(4)->create();
        Team::factory()->nfcNorth()->count(4)->create();
        Team::factory()->nfcSouth()->count(4)->create();
        Team::factory()->nfcWest()->count(4)->create();

        // Create players for each position using existing positions
        Player::factory()->count(10)->create(['position_id' => $qbPosition->id]);
        Player::factory()->count(15)->create(['position_id' => $rbPosition->id]);
        Player::factory()->count(20)->create(['position_id' => $wrPosition->id]);
        Player::factory()->count(12)->create(['position_id' => $tePosition->id]);
        Player::factory()->count(8)->create(['position_id' => $kPosition->id]);
        Player::factory()->count(16)->create(['position_id' => $dstPosition->id]);
    }
}
