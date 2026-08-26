<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per player per season, per part of the season.
 *
 * These totals arrive from their own file rather than being summed out of the
 * weekly rows, which is deliberate: two independently produced numbers that
 * agree are evidence the import is right, and one derived from the other is
 * not. Season totals also land in a single request, so a season is useful
 * before any weekly data exists.
 *
 * The per game and per attempt columns the old table carried are gone. They are
 * one division away in a query, and a stored average is wrong the moment a
 * weekly row is corrected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_stats_yearly');
        Schema::enableForeignKeyConstraints();

        Schema::create('player_stats_yearly', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->year('season');
            $table->string('season_type', 10)->default('regular');
            $table->string('source')->default('nflverse');

            // The team he finished the season with. A player who was traded
            // has one row here and a team per week in the weekly table, which
            // is where the question of who he played for is actually answered.
            $table->string('team_id', 10)->nullable();
            $table->string('position_id', 10)->nullable();
            $table->unsignedTinyInteger('games_played')->default(0);

            // Passing
            $table->unsignedSmallInteger('passing_attempts')->default(0);
            $table->unsignedSmallInteger('passing_completions')->default(0);
            $table->smallInteger('passing_yards')->default(0);
            $table->unsignedSmallInteger('passing_touchdowns')->default(0);
            $table->unsignedSmallInteger('passing_interceptions')->default(0);
            $table->unsignedSmallInteger('passing_sacks')->default(0);
            $table->integer('passing_air_yards')->default(0);
            $table->integer('passing_yards_after_catch')->default(0);
            $table->unsignedSmallInteger('passing_first_downs')->default(0);
            $table->unsignedSmallInteger('passing_two_point_conversions')->default(0);
            $table->decimal('passing_epa', 10, 3)->nullable();

            // Rushing
            $table->unsignedSmallInteger('rushing_attempts')->default(0);
            $table->integer('rushing_yards')->default(0);
            $table->unsignedSmallInteger('rushing_touchdowns')->default(0);
            $table->unsignedSmallInteger('rushing_first_downs')->default(0);
            $table->unsignedSmallInteger('rushing_two_point_conversions')->default(0);
            $table->decimal('rushing_epa', 10, 3)->nullable();

            // Receiving
            $table->unsignedSmallInteger('receiving_targets')->default(0);
            $table->unsignedSmallInteger('receiving_receptions')->default(0);
            $table->integer('receiving_yards')->default(0);
            $table->unsignedSmallInteger('receiving_touchdowns')->default(0);
            $table->integer('receiving_air_yards')->default(0);
            $table->integer('receiving_yards_after_catch')->default(0);
            $table->unsignedSmallInteger('receiving_first_downs')->default(0);
            $table->unsignedSmallInteger('receiving_two_point_conversions')->default(0);
            $table->decimal('receiving_epa', 10, 3)->nullable();

            $table->decimal('target_share', 6, 4)->nullable();
            $table->decimal('air_yards_share', 6, 4)->nullable();
            $table->decimal('wopr', 6, 4)->nullable();

            // Fumbles and returns
            $table->unsignedSmallInteger('fumbles')->default(0);
            $table->unsignedSmallInteger('fumbles_lost')->default(0);
            $table->unsignedSmallInteger('special_teams_touchdowns')->default(0);
            $table->unsignedSmallInteger('punt_returns')->default(0);
            $table->integer('punt_return_yards')->default(0);
            $table->unsignedSmallInteger('kickoff_returns')->default(0);
            $table->integer('kickoff_return_yards')->default(0);

            // Kicking
            $table->unsignedSmallInteger('field_goals_made')->default(0);
            $table->unsignedSmallInteger('field_goals_attempted')->default(0);
            $table->unsignedSmallInteger('field_goals_blocked')->default(0);
            $table->unsignedTinyInteger('field_goals_longest')->default(0);
            $table->unsignedSmallInteger('field_goals_made_0_19')->default(0);
            $table->unsignedSmallInteger('field_goals_made_20_29')->default(0);
            $table->unsignedSmallInteger('field_goals_made_30_39')->default(0);
            $table->unsignedSmallInteger('field_goals_made_40_49')->default(0);
            $table->unsignedSmallInteger('field_goals_made_50_59')->default(0);
            $table->unsignedSmallInteger('field_goals_made_60_plus')->default(0);
            $table->unsignedSmallInteger('extra_points_made')->default(0);
            $table->unsignedSmallInteger('extra_points_attempted')->default(0);

            $table->decimal('fantasy_points', 8, 2)->nullable();
            $table->decimal('fantasy_points_ppr', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['player_id', 'season', 'season_type', 'source'],
                'player_stats_yearly_unique'
            );

            $table->index(['season', 'season_type'], 'player_stats_yearly_season_index');

            $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_stats_yearly');
        Schema::enableForeignKeyConstraints();
    }
};
