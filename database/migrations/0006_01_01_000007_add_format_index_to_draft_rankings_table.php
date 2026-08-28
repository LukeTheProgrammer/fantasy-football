<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rankings are always read one season and one format at a time, and always
 * from the newest date that format holds. The table's only other index leads
 * with the player, which none of those reads know, so each of them scanned.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->index(
                ['season', 'type', 'ppr', 'superflex', 'source', 'ranked_at'],
                'draft_rankings_format_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_rankings', function (Blueprint $table) {
            $table->dropIndex('draft_rankings_format_index');
        });
    }
};
