<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_factory_creates_valid_position(): void
    {
        $position = Position::factory()->create();

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'abbreviation' => $position->abbreviation,
            'name' => $position->name,
        ]);

        $this->assertContains($position->abbreviation, ['QB', 'RB', 'WR', 'TE', 'K', 'DST']);
    }

    public function test_team_factory_creates_valid_team(): void
    {
        $team = Team::factory()->create();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'abbreviation' => $team->abbreviation,
            'location' => $team->location,
            'name' => $team->name,
            'conference' => $team->conference,
            'division' => $team->division,
        ]);

        $this->assertContains($team->conference, ['AFC', 'NFC']);
    }

    public function test_player_factory_creates_valid_player(): void
    {
        $player = Player::factory()->create();

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'first_name' => $player->first_name,
            'last_name' => $player->last_name,
            'position_id' => $player->position_id,
        ]);

        $this->assertNotNull($player->position);
        $this->assertInstanceOf(Position::class, $player->position);
    }

    public function test_position_specific_factories_work(): void
    {
        $qb = Position::factory()->quarterback()->create();
        $rb = Position::factory()->runningBack()->create();
        $wr = Position::factory()->wideReceiver()->create();

        $this->assertEquals('QB', $qb->abbreviation);
        $this->assertEquals('RB', $rb->abbreviation);
        $this->assertEquals('WR', $wr->abbreviation);
    }

    public function test_team_conference_factories_work(): void
    {
        $afcTeam = Team::factory()->afc()->create();
        $nfcTeam = Team::factory()->nfc()->create();

        $this->assertEquals('AFC', $afcTeam->conference);
        $this->assertEquals('NFC', $nfcTeam->conference);
    }

    public function test_player_position_factories_work(): void
    {
        $qb = Player::factory()->quarterback()->create();
        $rb = Player::factory()->runningBack()->create();

        $this->assertEquals('QB', $qb->position->abbreviation);
        $this->assertEquals('RB', $rb->position->abbreviation);
    }

    public function test_player_accessors_work(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $player->full_name);
    }

    public function test_team_accessors_work(): void
    {
        $team = Team::factory()->create([
            'location' => 'New York',
            'name' => 'Giants',
        ]);

        $this->assertEquals('New York Giants', $team->full_name);
    }
}
