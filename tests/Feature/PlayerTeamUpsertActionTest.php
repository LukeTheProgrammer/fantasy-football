<?php

namespace Tests\Feature;

use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTeamUpsertActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_team_a_player_was_on_in_a_season(): void
    {
        $player = Player::factory()->create();

        Action::model(PlayerTeam::class)->upsert($player, 'MIN', 2025);

        $this->assertDatabaseHas('player_teams', [
            'player_id'       => $player->id,
            'team_id'         => 'MIN',
            'season'          => 2025,
            'is_current_team' => true,
        ]);
    }

    public function test_a_season_of_its_own_does_not_disturb_an_earlier_one(): void
    {
        $player = Player::factory()->create();

        Action::model(PlayerTeam::class)->upsert($player, 'MIN', 2024);
        Action::model(PlayerTeam::class)->upsert($player, 'KC', 2025);

        // Importing a season must not rewrite the player's history, which is
        // what a sweep across every season used to do.
        $this->assertDatabaseHas('player_teams', [
            'player_id'       => $player->id,
            'team_id'         => 'MIN',
            'season'          => 2024,
            'is_current_team' => true,
        ]);
        $this->assertSame(2, $player->playerTeams()->count());
    }

    public function test_a_midseason_move_leaves_one_row_per_team(): void
    {
        $player = Player::factory()->create();

        Action::model(PlayerTeam::class)->upsert($player, 'NYJ', 2021);
        Action::model(PlayerTeam::class)->upsert($player, 'BAL', 2021);

        $this->assertSame(2, $player->playerTeams()->where('season', 2021)->count());
        $this->assertSame('BAL', $player->playerTeams()->where('is_current_team', true)->first()->team_id);
    }

    public function test_it_defaults_to_the_current_season(): void
    {
        Season::updateOrCreate(['id' => 2026], ['is_current' => true]);

        $player = Player::factory()->create();

        Action::model(PlayerTeam::class)->upsert($player, 'MIN');

        $this->assertSame(2026, (int) $player->playerTeams()->first()->season);
    }
}
