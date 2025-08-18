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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->integer('espn_id')->nullable();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('college')->nullable();
            $table->string('draft_year')->nullable();
            $table->string('draft_round')->nullable();
            $table->string('draft_pick')->nullable();
            $table->string('draft_team')->nullable();
            $table->timestamp('birth_date')->nullable();
            $table->string('headshot')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
