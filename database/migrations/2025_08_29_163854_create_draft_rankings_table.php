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
            $table->date('ranked_at');
            $table->enum('type', ['redraft', 'dynasty'])->default('redraft');
            $table->string('source')->nullable();
            $table->decimal('ppr', 6, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->integer('tier')->nullable();
            $table->decimal('adp', 6, 2)->nullable();
            $table->decimal('adv', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['player_id', 'year', 'ranked_at', 'type', 'source', 'ppr'], 'draft_rankings_unique');
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
