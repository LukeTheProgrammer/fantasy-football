<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CBS builds a team logo out of query parameters rather than storing an
     * image, so its urls run past the 255 characters ESPN's fit inside.
     */
    public function up(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->text('team_logo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->string('team_logo')->nullable()->change();
        });
    }
};
