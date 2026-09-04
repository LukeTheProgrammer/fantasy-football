<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pick draft does not have to reverse each round. CBS calls a straight
     * order "nonsnaking", and a board that reads it as a snake sends every
     * even round to the wrong team.
     */
    public function up(): void
    {
        Schema::table('drafts', function (Blueprint $table) {
            $table->enum('draft_type', ['snake', 'linear', 'auction'])->default('snake')->change();
        });
    }

    public function down(): void
    {
        DB::table('drafts')->where('draft_type', 'linear')->update(['draft_type' => 'snake']);

        Schema::table('drafts', function (Blueprint $table) {
            $table->enum('draft_type', ['snake', 'auction'])->default('snake')->change();
        });
    }
};
