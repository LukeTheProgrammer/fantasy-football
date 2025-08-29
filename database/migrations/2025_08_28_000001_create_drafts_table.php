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
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->dateTime('draft_date')->nullable();
            $table->enum('draft_type', ['snake', 'auction'])->default('snake');
            $table->boolean('is_completed')->default(false);
            $table->integer('auction_budget')->nullable();
            $table->integer('current_pick')->nullable();
            $table->integer('current_round')->nullable();
            $table->integer('time_per_pick')->default(90); // seconds
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
