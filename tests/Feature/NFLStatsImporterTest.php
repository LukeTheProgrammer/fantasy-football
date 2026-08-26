<?php

namespace Tests\Feature;

use App\Enums\Datum;
use App\Enums\SeasonType;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerStatWeekly;
use App\Services\Imports\Drivers\NFLStats\BaseNFLStatsDriver;
use App\Services\Imports\Importers\NFLStatsImporter;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The importer is the only place that decides which player a row of numbers
 * belongs to, so these tests are about identity rather than arithmetic.
 */
class NFLStatsImporterTest extends TestCase
{
    use RefreshDatabase;

    private function importer(array $players = [], array $games = [], array $stats = []): NFLStatsImporter
    {
        return new NFLStatsImporter(new class($players, $games, $stats) extends BaseNFLStatsDriver
        {
            public function __construct(
                private array $playerRows,
                private array $gameRows,
                private array $statRows,
            ) {
                //
            }

            public function players(): Generator
            {
                yield from $this->playerRows;
            }

            public function games(?int $season = null): Generator
            {
                yield from $this->gameRows;
            }

            public function stats(int $season, string $window): Generator
            {
                yield from $this->statRows;
            }
        });
    }

    private function statRow(array $overrides = []): array
    {
        return array_merge([
            'gsis_id'              => '00-0036322',
            'full_name'            => 'Justin Jefferson',
            'season'               => 2025,
            'week'                 => 4,
            'season_type'          => SeasonType::REGULAR,
            'source'               => Datum::SOURCE_NFLVERSE->value,
            'team_id'              => 'MIN',
            'opponent_team_id'     => 'PIT',
            'position_id'          => 'WR',
            'nflverse_game_id'     => '2025_04_MIN_PIT',
            'receiving_receptions' => 10,
            'receiving_yards'      => 126,
        ], $overrides);
    }

    public function test_it_writes_a_stat_line_for_a_known_player(): void
    {
        $player = Player::factory()->create(['gsis_id' => '00-0036322', 'position_id' => 'WR']);

        $result = $this->importer(stats: [$this->statRow()])->importStats(2025, 'week');

        $this->assertSame(1, $result['written']);
        $this->assertDatabaseHas('player_stats_weekly', [
            'player_id'        => $player->id,
            'season'           => 2025,
            'week'             => 4,
            'receiving_yards'  => 126,
            'opponent_team_id' => 'PIT',
        ]);
    }

    public function test_importing_the_same_season_twice_does_not_double_the_rows(): void
    {
        Player::factory()->create(['gsis_id' => '00-0036322', 'position_id' => 'WR']);

        $this->importer(stats: [$this->statRow()])->importStats(2025, 'week');
        $this->importer(stats: [$this->statRow(['receiving_yards' => 130])])->importStats(2025, 'week');

        $this->assertSame(1, PlayerStatWeekly::count());
        $this->assertSame(130, PlayerStatWeekly::first()->receiving_yards);
    }

    public function test_a_stat_line_is_never_matched_to_a_player_by_name(): void
    {
        // Three men called Josh Johnson took a snap in 2021. A line resolved by
        // name would put one man's season on another's record.
        $quarterback = Player::factory()->create([
            'gsis_id'     => '00-0026300',
            'full_name'   => 'Josh Johnson',
            'position_id' => 'QB',
        ]);

        $result = $this->importer(stats: [$this->statRow([
            'gsis_id'     => '00-0036799',
            'full_name'   => 'Josh Johnson',
            'position_id' => 'RB',
        ])])->importStats(2021, 'week');

        $this->assertSame(0, $result['written']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, $quarterback->weeklyStats()->count());
        $this->assertSame('Player not found', $result['errors'][0]['reason']);
    }

    public function test_it_skips_positions_a_fantasy_league_does_not_score(): void
    {
        Player::factory()->create(['gsis_id' => '00-0036322', 'position_id' => 'WR']);

        $result = $this->importer(stats: [$this->statRow(['position_id' => 'LB'])])->importStats(2025, 'week');

        $this->assertSame(0, $result['written']);
        $this->assertSame(0, PlayerStatWeekly::count());
    }

    public function test_a_stat_line_finds_the_game_it_was_played_in(): void
    {
        Player::factory()->create(['gsis_id' => '00-0036322', 'position_id' => 'WR']);

        $game = NflGame::create([
            'nflverse_id'  => '2025_04_MIN_PIT',
            'season'       => 2025,
            'week'         => 4,
            'home_team_id' => 'PIT',
            'away_team_id' => 'MIN',
        ]);

        $this->importer(stats: [$this->statRow()])->importStats(2025, 'week');

        $this->assertSame($game->id, PlayerStatWeekly::first()->nfl_game_id);
    }

    public function test_a_namesake_with_his_own_id_becomes_his_own_player(): void
    {
        Player::factory()->create([
            'gsis_id'     => '00-0026300',
            'full_name'   => 'Josh Johnson',
            'first_name'  => 'Josh',
            'last_name'   => 'Johnson',
            'position_id' => 'QB',
        ]);

        $result = $this->importer(players: [[
            'gsis_id'     => '00-0036799',
            'full_name'   => 'Josh Johnson',
            'first_name'  => 'Josh',
            'last_name'   => 'Johnson',
            'position_id' => 'RB',
            'last_season' => '2021',
        ]])->importPlayers(2021);

        $this->assertSame(1, $result['written']);
        $this->assertSame(2, Player::where('full_name', 'Josh Johnson')->count());
    }

    public function test_a_player_the_app_already_has_is_linked_rather_than_duplicated(): void
    {
        $player = Player::factory()->create([
            'espn_id'     => 4262921,
            'gsis_id'     => null,
            'full_name'   => 'Justin Jefferson',
            'position_id' => 'WR',
        ]);

        $this->importer(players: [[
            'gsis_id'     => '00-0036322',
            'espn_id'     => '4262921',
            'pfr_id'      => 'JeffJu00',
            'full_name'   => 'Justin Jefferson',
            'position_id' => 'WR',
            'last_season' => '2025',
        ]])->importPlayers(2021);

        $this->assertSame(1, Player::where('full_name', 'Justin Jefferson')->count());
        $this->assertSame('00-0036322', $player->fresh()->gsis_id);
        $this->assertSame('JeffJu00', $player->fresh()->pfr_id);
    }

    public function test_an_id_another_player_already_holds_is_reported_rather_than_forced(): void
    {
        Player::factory()->create(['pfr_id' => 'JeffJu00', 'position_id' => 'WR', 'gsis_id' => null]);
        $other = Player::factory()->create([
            'espn_id'     => 4262921,
            'gsis_id'     => null,
            'pfr_id'      => null,
            'full_name'   => 'Justin Jefferson',
            'position_id' => 'WR',
        ]);

        $result = $this->importer(players: [[
            'gsis_id'     => '00-0036322',
            'espn_id'     => '4262921',
            'pfr_id'      => 'JeffJu00',
            'full_name'   => 'Justin Jefferson',
            'position_id' => 'WR',
            'last_season' => '2025',
        ]])->importPlayers(2021);

        $this->assertNull($other->fresh()->pfr_id);
        $this->assertSame('Conflicting pfr_id', $result['errors'][0]['reason']);
    }

    public function test_it_records_the_ids_other_sources_use_for_a_game(): void
    {
        $game = NflGame::create([
            'espn_id'      => 401772510,
            'season'       => 2025,
            'week'         => 1,
            'home_team_id' => 'PHI',
            'away_team_id' => 'DAL',
        ]);

        $this->importer(games: [[
            'nflverse_id'  => '2025_01_DAL_PHI',
            'espn_id'      => '401772510',
            'pfr_id'       => '202509040phi',
            'season'       => 2025,
            'week'         => 1,
            'season_type'  => SeasonType::REGULAR,
            'is_playoff'   => false,
            'home_team_id' => 'PHI',
            'away_team_id' => 'DAL',
            'home_score'   => '24',
            'away_score'   => '20',
            'is_completed' => true,
            'starts_at'    => null,
            'is_bye'       => false,
        ]])->importGames(2025);

        $this->assertSame(1, NflGame::count());
        $this->assertSame('2025_01_DAL_PHI', $game->fresh()->nflverse_id);
        $this->assertSame('202509040phi', $game->fresh()->pfr_id);
    }
}
