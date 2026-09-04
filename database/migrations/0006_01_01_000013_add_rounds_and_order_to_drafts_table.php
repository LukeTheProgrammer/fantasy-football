<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pick draft needs to know how many rounds it runs and who owns each
     * slot. CBS lets a commissioner set the order by hand, so it cannot be
     * derived from the member list and has to be carried.
     */
    public function up(): void
    {
        Schema::table('drafts', function (Blueprint $table) {
            $table->unsignedSmallInteger('rounds')->default(0)->after('draft_type');
            // The external team id of each slot, in overall pick order.
            $table->json('draft_order')->nullable()->after('rounds');
        });
    }

    public function down(): void
    {
        Schema::table('drafts', function (Blueprint $table) {
            $table->dropColumn(['rounds', 'draft_order']);
        });
    }
};
