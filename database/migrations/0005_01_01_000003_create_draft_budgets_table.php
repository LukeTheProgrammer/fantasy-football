<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A team's plan for how to spend its auction budget, slot by slot.
 *
 * The plan is written once and kept whole, so it is stored as a single row per
 * team rather than a row per slot: it is only ever read and saved together.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('draft_budgets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_member_id')->constrained()->cascadeOnDelete();

            // Planned dollars keyed by roster slot name ("QB1", "RB2", "BE3").
            $table->json('allocations');

            $table->timestamps();

            $table->unique(['draft_id', 'league_member_id'], 'draft_budgets_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_budgets');
    }
};
