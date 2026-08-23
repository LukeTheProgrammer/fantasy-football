<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the four per scoring format column families with one row per format.
 *
 * The table starts empty and is repopulated from the data archive, so no
 * conversion of the old wide rows is attempted here.
 */
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

            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nfl_game_id')->nullable()->constrained('nfl_games')->cascadeOnDelete();

            $table->year('season');
            $table->unsignedInteger('week')->default(0);

            $table->string('source');

            // The scoring format the projection was made under.
            $table->decimal('ppr', 3, 2)->default(0);
            $table->boolean('superflex')->default(false);

            $table->decimal('projected_points', 10, 2)->nullable();
            $table->unsignedInteger('pos_rank')->nullable();
            $table->unsignedInteger('pos_rank_min')->nullable();
            $table->unsignedInteger('pos_rank_max')->nullable();
            $table->decimal('pos_rank_avg', 8, 2)->nullable();
            $table->decimal('pos_rank_std', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['player_id', 'season', 'week', 'source', 'ppr', 'superflex'],
                'player_projections_unique'
            );

            $table->index(['season', 'week', 'source'], 'player_projections_week_source_index');
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
