<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The platform cookies a league is read with. The model has cast this for
     * a while, and the working database has the column, but no migration ever
     * added it, so a fresh database did not.
     */
    public function up(): void
    {
        if (Schema::hasColumn('leagues', 'credentials')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            $table->json('credentials')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('leagues', 'credentials')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('credentials');
        });
    }
};
