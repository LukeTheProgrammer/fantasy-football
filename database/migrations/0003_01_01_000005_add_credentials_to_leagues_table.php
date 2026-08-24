<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The platform credentials a league was imported with, kept on the league
     * so a later sync can repeat the pull without being handed the cookies
     * again. Nullable because a league created by hand has none.
     *
     * Text rather than json: the model encrypts these, and a ciphertext string
     * is not valid json.
     */
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->text('credentials')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('credentials');
        });
    }
};
