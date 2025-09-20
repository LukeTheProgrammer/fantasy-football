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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('league_member_rosters');
        Schema::enableForeignKeyConstraints();

        Schema::create('league_member_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_member_id')->constrained('league_members')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('nfl_game_id')->nullable()->constrained('nfl_games')->cascadeOnDelete();
            $table->year('season')->default(now()->year);
            $table->unsignedInteger('week')->default(0);
            $table->unsignedInteger('lineup_slot_id')->default(0);
            $table->unsignedInteger('position_rank')->default(999999);
            $table->unsignedInteger('overall_rank')->default(999999);
            $table->decimal('fantasy_points', 10, 2)->default(0);
            $table->decimal('espn_projected_points', 10, 2)->default(0);
            $table->decimal('percent_owned', 10, 2)->default(0);
            $table->decimal('percent_started', 10, 2)->default(0);
            $table->decimal('percent_changed', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['league_member_id', 'player_id', 'nfl_game_id'], 'league_member_rosters_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('league_member_rosters');
        Schema::enableForeignKeyConstraints();
    }
};
