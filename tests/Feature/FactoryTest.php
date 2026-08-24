<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Database\Factories\PlayerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_positions_are_seeded_rather_than_generated(): void
    {
        $this->assertSame(17, Position::count());

        foreach (PlayerFactory::FANTASY_POSITIONS as $abbreviation) {
            $this->assertDatabaseHas('positions', ['id' => $abbreviation, 'abbreviation' => $abbreviation]);
        }
    }

    public function test_teams_are_seeded_rather_than_generated(): void
    {
        $this->assertSame(33, Team::count());

        $this->assertDatabaseHas('teams', [
            'id'         => 'KC',
            'location'   => 'Kansas City',
            'name'       => 'Chiefs',
            'conference' => 'AFC',
        ]);
    }

    public function test_player_factory_creates_valid_player(): void
    {
        $player = Player::factory()->create();

        $this->assertDatabaseHas('players', [
            'id'          => $player->id,
            'first_name'  => $player->first_name,
            'last_name'   => $player->last_name,
            'position_id' => $player->position_id,
        ]);

        $this->assertInstanceOf(Position::class, $player->position);
        $this->assertContains($player->position_id, PlayerFactory::FANTASY_POSITIONS);
    }

    public function test_player_position_factories_work(): void
    {
        $qb = Player::factory()->quarterback()->create();
        $rb = Player::factory()->runningBack()->create();

        $this->assertEquals('QB', $qb->position->abbreviation);
        $this->assertEquals('RB', $rb->position->abbreviation);
    }

    public function test_players_reuse_the_seeded_positions(): void
    {
        // Forty players repeat positions many times over. Because positions are
        // reference rows rather than generated ones, that is a lookup rather
        // than a duplicate key error.
        $players = Player::factory()->count(40)->create();

        $this->assertCount(40, $players);
        $this->assertSame(17, Position::count());
        $this->assertSame(
            $players->pluck('position_id')->unique()->count(),
            Position::whereIn('id', $players->pluck('position_id')->unique())->count(),
        );
    }

    public function test_player_accessors_work(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $this->assertEquals('John Doe', $player->full_name);
    }

    public function test_team_accessors_work(): void
    {
        $team = Team::find('NYG');

        $this->assertEquals('New York Giants', $team->full_name);
    }
}
