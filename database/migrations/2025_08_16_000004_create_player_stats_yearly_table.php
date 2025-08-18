<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_stats_yearly', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();

            $table->integer('games_played')->default(0);
            $table->integer('games_started')->default(0);

            $table->integer('passing_attempts')->default(0);
            $table->integer('passing_completions')->default(0);
            $table->decimal('passing_completion_percentage', 6, 2)->default(0);
            $table->integer('passing_yards')->default(0);
            $table->integer('passing_touchdowns')->default(0);
            $table->integer('passing_interceptions')->default(0);
            $table->decimal('passing_attempts_per_game', 6, 2)->default(0);
            $table->decimal('passing_yards_per_attempt', 6, 2)->default(0);
            $table->decimal('passing_yards_per_game', 6, 2)->default(0);

            $table->integer('rushing_attempts')->default(0);
            $table->integer('rushing_yards')->default(0);
            $table->integer('rushing_yards_longest')->default(0);
            $table->integer('rushing_touchdowns')->default(0);
            $table->decimal('rushing_attempts_per_game', 6, 2)->default(0);
            $table->decimal('rushing_yards_per_attempt', 6, 2)->default(0);
            $table->decimal('rushing_yards_per_game', 6, 2)->default(0);

            $table->integer('receiving_targets')->default(0);
            $table->integer('receiving_receptions')->default(0);
            $table->integer('receiving_yards')->default(0);
            $table->integer('receiving_yards_longest')->default(0);
            $table->integer('receiving_touchdowns')->default(0);
            $table->decimal('receiving_attempts_per_game', 6, 2)->default(0);
            $table->decimal('receiving_yards_per_catch', 6, 2)->default(0);
            $table->decimal('receiving_yards_per_game', 6, 2)->default(0);

            $table->integer('fumbles')->default(0);
            $table->integer('fumbles_lost')->default(0);
            $table->decimal('fumbles_per_game', 6, 2)->default(0);
            $table->decimal('fumbles_lost_per_game', 6, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_stats_yearly');
    }
};
