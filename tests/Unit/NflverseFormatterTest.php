<?php

namespace Tests\Unit;

use App\Enums\SeasonType;
use App\Services\Nflverse\Extractors\PlayerStatsExtractor;
use App\Services\Nflverse\Formatters\GameFormatter;
use App\Services\Nflverse\Formatters\PlayerFormatter;
use App\Services\Nflverse\Formatters\PlayerStatsFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Pins the translation between the source's column names and the app's.
 *
 * These are the mappings a rename upstream would break silently: the numbers
 * would still import, they would just land in the wrong column.
 */
class NflverseFormatterTest extends TestCase
{
    public function test_a_weekly_line_maps_onto_the_stat_columns(): void
    {
        $row = [
            'player_id'           => '00-0036322',
            'player_display_name' => 'Justin Jefferson',
            'position'            => 'WR',
            'season_type'         => 'REG',
            'week'                => '4',
            'game_id'             => '2025_04_MIN_PIT',
            'team'                => 'MIN',
            'opponent_team'       => 'PIT',
            'receptions'          => '10',
            'targets'             => '14',
            'receiving_yards'     => '126',
            'receiving_tds'       => '0',
            'carries'             => '1',
            'rushing_yards'       => '-3',
            'target_share'        => '0.2619',
            'fantasy_points_ppr'  => '22.6',
        ];

        $extracted = (new PlayerStatsExtractor)->extract($row, 2025);
        $formatted = (new PlayerStatsFormatter)->format($extracted);

        $this->assertSame(2025, $formatted['season']);
        $this->assertSame(4, $formatted['week']);
        $this->assertSame(SeasonType::REGULAR, $formatted['season_type']);
        $this->assertSame('MIN', $formatted['team_id']);
        $this->assertSame('PIT', $formatted['opponent_team_id']);
        $this->assertSame('2025_04_MIN_PIT', $formatted['nflverse_game_id']);
        $this->assertSame(10, $formatted['receiving_receptions']);
        $this->assertSame(14, $formatted['receiving_targets']);
        $this->assertSame(126, $formatted['receiving_yards']);
        $this->assertSame(0.2619, $formatted['target_share']);
        $this->assertSame(22.6, $formatted['fantasy_points_ppr']);
    }

    public function test_negative_yardage_survives_extraction(): void
    {
        $extracted = (new PlayerStatsExtractor)->extract(['rushing_yards' => '-3'], 2025);

        $this->assertSame(-3, $extracted['rushing_yards']);
    }

    public function test_a_blank_counting_stat_is_zero_and_a_blank_rate_is_null(): void
    {
        $extracted = (new PlayerStatsExtractor)->extract([
            'receptions'   => '',
            'target_share' => '',
        ], 2025);

        $this->assertSame(0, $extracted['receptions']);
        $this->assertNull($extracted['target_share']);
    }

    public function test_a_season_line_carries_games_rather_than_a_week(): void
    {
        $extracted = (new PlayerStatsExtractor)->extract([
            'player_id'   => '00-0036322',
            'season_type' => 'REG',
            'recent_team' => 'MIN',
            'games'       => '17',
        ], 2025, weekly: false);

        $formatted = (new PlayerStatsFormatter)->format($extracted, weekly: false);

        $this->assertSame(17, $formatted['games_played']);
        $this->assertArrayNotHasKey('week', $formatted);
        $this->assertSame('MIN', $formatted['team_id']);
    }

    public function test_the_postseason_is_recognised_however_it_is_spelled(): void
    {
        foreach (['POST', 'WC', 'DIV', 'CON', 'SB'] as $label) {
            $this->assertSame(SeasonType::POST, SeasonType::fromSource($label), $label);
        }

        $this->assertSame(SeasonType::REGULAR, SeasonType::fromSource('REG'));
    }

    public function test_team_abbreviations_are_translated_to_the_apps_own(): void
    {
        $extracted = (new PlayerStatsExtractor)->extract([
            'team'          => 'LA',
            'opponent_team' => 'WAS',
        ], 2024);

        $this->assertSame('LAR', $extracted['team']);
        $this->assertSame('WSH', $extracted['opponent_team']);
    }

    public function test_a_game_records_every_sources_id_for_it(): void
    {
        $game = (new GameFormatter)->format([
            'game_id'    => '2025_01_DAL_PHI',
            'season'     => '2025',
            'game_type'  => 'REG',
            'week'       => '1',
            'gameday'    => '2025-09-04',
            'gametime'   => '20:20',
            'away_team'  => 'DAL',
            'home_team'  => 'PHI',
            'away_score' => '20',
            'home_score' => '24',
            'espn'       => '401772510',
            'pfr'        => '202509040phi',
        ]);

        $this->assertSame('2025_01_DAL_PHI', $game['nflverse_id']);
        $this->assertSame('401772510', $game['espn_id']);
        $this->assertSame('202509040phi', $game['pfr_id']);
        $this->assertSame('PHI', $game['home_team_id']);
        $this->assertFalse($game['is_playoff']);
        $this->assertTrue($game['is_completed']);

        // Kickoff is published on the league's Eastern clock and stored in UTC.
        $this->assertSame('2025-09-05 00:20:00', $game['starts_at']->toDateTimeString());
    }

    public function test_a_playoff_game_is_flagged(): void
    {
        $game = (new GameFormatter)->format([
            'game_id'   => '2025_20_BUF_KC',
            'season'    => '2025',
            'game_type' => 'CON',
            'week'      => '20',
            'gameday'   => '2026-01-25',
            'home_team' => 'KC',
            'away_team' => 'BUF',
        ]);

        $this->assertTrue($game['is_playoff']);
        $this->assertFalse($game['is_completed']);
    }

    public function test_a_player_arrives_with_height_and_weight_in_the_apps_own_units(): void
    {
        $player = (new PlayerFormatter)->format([
            'gsis_id'      => '00-0036322',
            'display_name' => 'Justin Jefferson',
            'position'     => 'WR',
            'height'       => '73',
            'weight'       => '195',
            'latest_team'  => 'MIN',
            'pfr_id'       => 'JeffJu00',
            'espn_id'      => '4262921',
        ]);

        $this->assertSame("6' 1\"", $player['height']);
        $this->assertSame('195 lbs', $player['weight']);
        $this->assertSame('MIN', $player['team_id']);
        $this->assertSame('JeffJu00', $player['pfr_id']);
    }

    public function test_positions_the_app_spells_differently_are_translated(): void
    {
        $formatter = new PlayerFormatter;

        $this->assertSame('S', $formatter->format(['position' => 'SAF'])['position_id']);
        $this->assertSame('RB', $formatter->format(['position' => 'HB'])['position_id']);
        $this->assertSame('WR', $formatter->format(['position' => 'WR'])['position_id']);
    }
}
