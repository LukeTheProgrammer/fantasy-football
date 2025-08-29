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
        Schema::dropIfExists('draft_rankings');

        Schema::create('draft_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->year('year');
            $table->decimal('average_rank', 6, 2)->nullable();
            $table->decimal('average_value', 6, 2)->nullable();
            $table->integer('fp_ranking')->nullable();
            $table->integer('fp_tier')->nullable();
            $table->decimal('fp_adp', 6, 2)->nullable();
            $table->decimal('fp_adv', 6, 2)->nullable();
            $table->decimal('fp_ecr_vs_adp', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['player_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_rankings');
    }
};
