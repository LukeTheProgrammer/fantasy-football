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
        Schema::create('player_ranking_averages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->year('season');
            $table->date('ranked_on');
            $table->enum('type', ['redraft', 'dynasty'])->default('redraft');
            $table->decimal('ppr', 6, 2)->default(0);
            $table->decimal('rank', 8, 2)->nullable();
            $table->decimal('tier', 6, 2)->nullable();
            $table->decimal('adp', 8, 2)->nullable();
            $table->decimal('adv', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['player_id', 'season', 'ranked_on', 'type', 'ppr'],
                'player_ranking_averages_unique'
            );

            // Time series reads: one player's value across dates.
            $table->index(['player_id', 'ranked_on'], 'player_ranking_averages_player_date_index');

            // Leaderboard reads: everyone's value on a given date.
            $table->index(['season', 'ranked_on', 'type', 'ppr'], 'player_ranking_averages_date_format_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_ranking_averages');
    }
};
