<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per player per game.
 *
 * The table it replaces was never written to and could not be: it had no
 * season, no link to a game, and no unique key to upsert against. This one
 * carries the game, the team he played for that week and the team he played
 * against, so a stat line answers questions about matchups and not just totals.
 *
 * Stored stats are counting stats plus the opportunity measures (targets, air
 * yards, snap share) that say whether a good week was earned or lucky. Fantasy
 * points are kept as the source computed them, for cross-checking only — a
 * league's own scoring is applied from LeagueSettings, not from here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_stats_weekly');
        Schema::enableForeignKeyConstraints();

        Schema::create('player_stats_weekly', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->year('season');
            $table->unsignedTinyInteger('week');
            $table->string('season_type', 10)->default('regular');
            $table->string('source')->default('nflverse');

            $table->foreignId('nfl_game_id')->nullable()->constrained('nfl_games')->nullOnDelete();
            $table->string('nflverse_game_id')->nullable();

            $table->string('team_id', 10)->nullable();
            $table->string('opponent_team_id', 10)->nullable();
            $table->string('position_id', 10)->nullable();

            // Passing
            $table->unsignedSmallInteger('passing_attempts')->default(0);
            $table->unsignedSmallInteger('passing_completions')->default(0);
            $table->smallInteger('passing_yards')->default(0);
            $table->unsignedTinyInteger('passing_touchdowns')->default(0);
            $table->unsignedTinyInteger('passing_interceptions')->default(0);
            $table->unsignedTinyInteger('passing_sacks')->default(0);
            $table->smallInteger('passing_air_yards')->default(0);
            $table->smallInteger('passing_yards_after_catch')->default(0);
            $table->unsignedSmallInteger('passing_first_downs')->default(0);
            $table->unsignedTinyInteger('passing_two_point_conversions')->default(0);
            $table->decimal('passing_epa', 8, 3)->nullable();

            // Rushing
            $table->unsignedSmallInteger('rushing_attempts')->default(0);
            $table->smallInteger('rushing_yards')->default(0);
            $table->unsignedTinyInteger('rushing_touchdowns')->default(0);
            $table->unsignedSmallInteger('rushing_first_downs')->default(0);
            $table->unsignedTinyInteger('rushing_two_point_conversions')->default(0);
            $table->decimal('rushing_epa', 8, 3)->nullable();

            // Receiving
            $table->unsignedSmallInteger('receiving_targets')->default(0);
            $table->unsignedSmallInteger('receiving_receptions')->default(0);
            $table->smallInteger('receiving_yards')->default(0);
            $table->unsignedTinyInteger('receiving_touchdowns')->default(0);
            $table->smallInteger('receiving_air_yards')->default(0);
            $table->smallInteger('receiving_yards_after_catch')->default(0);
            $table->unsignedSmallInteger('receiving_first_downs')->default(0);
            $table->unsignedTinyInteger('receiving_two_point_conversions')->default(0);
            $table->decimal('receiving_epa', 8, 3)->nullable();

            // Opportunity share, which is what separates a role from a game script.
            $table->decimal('target_share', 6, 4)->nullable();
            $table->decimal('air_yards_share', 6, 4)->nullable();
            $table->decimal('wopr', 6, 4)->nullable();

            // Fumbles and returns
            $table->unsignedTinyInteger('fumbles')->default(0);
            $table->unsignedTinyInteger('fumbles_lost')->default(0);
            $table->unsignedTinyInteger('special_teams_touchdowns')->default(0);
            $table->unsignedTinyInteger('punt_returns')->default(0);
            $table->smallInteger('punt_return_yards')->default(0);
            $table->unsignedTinyInteger('kickoff_returns')->default(0);
            $table->smallInteger('kickoff_return_yards')->default(0);

            // Kicking, bucketed by distance because leagues score it that way.
            $table->unsignedTinyInteger('field_goals_made')->default(0);
            $table->unsignedTinyInteger('field_goals_attempted')->default(0);
            $table->unsignedTinyInteger('field_goals_blocked')->default(0);
            $table->unsignedTinyInteger('field_goals_longest')->default(0);
            $table->unsignedTinyInteger('field_goals_made_0_19')->default(0);
            $table->unsignedTinyInteger('field_goals_made_20_29')->default(0);
            $table->unsignedTinyInteger('field_goals_made_30_39')->default(0);
            $table->unsignedTinyInteger('field_goals_made_40_49')->default(0);
            $table->unsignedTinyInteger('field_goals_made_50_59')->default(0);
            $table->unsignedTinyInteger('field_goals_made_60_plus')->default(0);
            $table->unsignedTinyInteger('extra_points_made')->default(0);
            $table->unsignedTinyInteger('extra_points_attempted')->default(0);

            // Snap share arrives from a second file, so it stays nullable.
            $table->unsignedSmallInteger('offense_snaps')->nullable();
            $table->decimal('offense_snap_percentage', 6, 4)->nullable();

            // The source's own scoring, kept only to check ours against.
            $table->decimal('fantasy_points', 8, 2)->nullable();
            $table->decimal('fantasy_points_ppr', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['player_id', 'season', 'week', 'season_type', 'source'],
                'player_stats_weekly_unique'
            );

            $table->index(['season', 'week'], 'player_stats_weekly_season_week_index');
            $table->index(['team_id', 'season'], 'player_stats_weekly_team_season_index');

            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('opponent_team_id')->references('id')->on('teams');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_stats_weekly');
        Schema::enableForeignKeyConstraints();
    }
};
