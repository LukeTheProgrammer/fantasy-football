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
        Schema::create('league_member_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_member_id')->constrained()->onDelete('cascade');

            $table->text('espn_s2')->nullable();
            $table->text('espn_swid')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['league_id', 'league_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_member_credentials');
    }
};
