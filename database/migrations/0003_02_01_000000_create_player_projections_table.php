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
        Schema::dropIfExists('player_projections');
        Schema::enableForeignKeyConstraints();

        Schema::create('player_projections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('nfl_game_id')->nullable()->constrained('nfl_games')->cascadeOnDelete();

            $table->year('season')->default(now()->year);
            $table->unsignedInteger('week')->default(0);

            $table->decimal('espn_projected_points', 10, 2)->default(0);

            $table->decimal('fp_projected_points', 10, 2)->default(0);
            $table->unsignedInteger('fp_pos_rank')->nullable();
            $table->unsignedInteger('fp_pos_rank_min')->nullable();
            $table->unsignedInteger('fp_pos_rank_max')->nullable();
            $table->unsignedInteger('fp_pos_rank_avg')->nullable();
            $table->unsignedInteger('fp_pos_rank_std')->nullable();

            $table->decimal('fp_half_projected_points', 10, 2)->default(0);
            $table->unsignedInteger('fp_half_pos_rank')->nullable();
            $table->unsignedInteger('fp_half_pos_rank_min')->nullable();
            $table->unsignedInteger('fp_half_pos_rank_max')->nullable();
            $table->unsignedInteger('fp_half_pos_rank_avg')->nullable();
            $table->unsignedInteger('fp_half_pos_rank_std')->nullable();

            $table->decimal('fp_ppr_projected_points', 10, 2)->default(0);
            $table->unsignedInteger('fp_ppr_pos_rank')->nullable();
            $table->unsignedInteger('fp_ppr_pos_rank_min')->nullable();
            $table->unsignedInteger('fp_ppr_pos_rank_max')->nullable();
            $table->unsignedInteger('fp_ppr_pos_rank_avg')->nullable();
            $table->unsignedInteger('fp_ppr_pos_rank_std')->nullable();

            $table->decimal('fp_2qb_projected_points', 10, 2)->default(0);
            $table->unsignedInteger('fp_2qb_pos_rank')->nullable();
            $table->unsignedInteger('fp_2qb_pos_rank_min')->nullable();
            $table->unsignedInteger('fp_2qb_pos_rank_max')->nullable();
            $table->unsignedInteger('fp_2qb_pos_rank_avg')->nullable();
            $table->unsignedInteger('fp_2qb_pos_rank_std')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('player_projections');
        Schema::enableForeignKeyConstraints();
    }
};
