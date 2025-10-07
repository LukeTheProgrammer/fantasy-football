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
        Schema::dropIfExists('draft_picks');
        Schema::enableForeignKeyConstraints();

        Schema::create('draft_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->integer('round')->default(0);
            $table->integer('pick_number')->default(0);
            $table->integer('overall_pick_number')->default(0);
            $table->decimal('amount', 10, 2)->nullable(); // For auction drafts
            $table->boolean('is_keeper')->default(false);
            $table->timestamps();

            // Ensure pick numbers are unique within a draft
            $table->unique(['draft_id', 'round', 'pick_number']);

            // Ensure players are only drafted once
            $table->unique(['draft_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('draft_picks');
        Schema::enableForeignKeyConstraints();
    }
};
