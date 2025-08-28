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
        Schema::create('draft_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('pick_number');
            $table->integer('round');
            $table->decimal('amount', 10, 2)->nullable(); // For auction drafts
            $table->boolean('is_keeper')->default(false);
            $table->decimal('previous_year_cost', 10, 2)->nullable(); // For keepers
            $table->dateTime('pick_time')->nullable();
            $table->timestamps();
            
            // Ensure pick numbers are unique within a draft
            $table->unique(['draft_id', 'pick_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_picks');
    }
};
