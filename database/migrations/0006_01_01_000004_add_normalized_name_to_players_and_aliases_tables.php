<?php

use App\Services\Player\Helpers\NormalizedName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores each name in the reduced form sources can be compared on.
 *
 * Not unique: two players really can reduce to the same name, and that
 * collision is the thing a lookup needs to see so it can decline to guess.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index()->after('full_name');
        });

        Schema::table('player_aliases', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index()->after('name');
        });

        $this->backfill('players', 'full_name');
        $this->backfill('player_aliases', 'name');
    }

    private function backfill(string $table, string $column): void
    {
        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(500, function ($rows) use ($table, $column) {
            foreach ($rows as $row) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['normalized_name' => NormalizedName::of($row->{$column})]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['normalized_name']);
            $table->dropColumn('normalized_name');
        });

        Schema::table('player_aliases', function (Blueprint $table) {
            $table->dropIndex(['normalized_name']);
            $table->dropColumn('normalized_name');
        });
    }
};
