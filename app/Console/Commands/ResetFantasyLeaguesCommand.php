<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetFantasyLeaguesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-fantasy-leagues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets all the data in the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // $this->rebuildTables();

        $this->rebuildData();

        $this->call('import:fantasy:league', [
            '--create' => true,
            '--creator-id' => 1,
            '--season' => 2025,
            '--platform' => 'ESPN',
            '--platform-id' => 691509, // Milhaven
            '--espn-s2' => config('services.espn.default_s2'),
            '--espn-swid' => config('services.espn.default_swid'),
        ]);

        // $this->call('import:fantasy:league', [
        //     '--sync' => true,
        //     '--league-id' => 1,
        //     '--season' => 2025,
        // ]);

        $this->call('import:fantasy:roster', ['leagueId' => 1, 'season' => 2025]);

        $this->call('import:fantasy:league', [
            '--create' => true,
            '--creator-id' => 1,
            '--season' => 2025,
            '--platform' => 'ESPN',
            '--platform-id' => 61235367, // HawkHorns
            '--espn-s2' => config('services.espn.default_s2'),
            '--espn-swid' => config('services.espn.default_swid'),
        ]);

        // $this->call('import:fantasy:league', [
        //     '--sync' => true,
        //     '--league-id' => 2,
        //     '--season' => 2025,
        // ]);

        $this->call('import:fantasy:roster', ['leagueId' => 2, 'season' => 2025]);

        return Command::SUCCESS;
    }

    private function rebuildTables()
    {
        $migrations = [
            '0003_01_01_000000_create_leagues_table.php',
            '0003_01_01_000001_create_league_settings_table.php',
            '0003_01_01_000002_create_league_members_table.php',
            '0003_01_01_000003_create_league_member_rosters_table.php',
            '0003_01_01_000004_create_league_matchups_table.php',
            '0004_01_01_000000_create_drafts_table.php',
            '0004_01_01_000001_create_draft_picks_table.php',
            '0004_01_01_000002_create_draft_rankings_table.php',
        ];

        foreach ($migrations as $migration) {
            $this->info("Rolling back {$migration}");
            $this->call('migrate:rollback', ['--path' => "database/migrations/{$migration}"]);
        }

        $this->call('migrate');
    }

    private function rebuildData()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('leagues')->truncate();
        DB::table('league_settings')->truncate();
        DB::table('league_members')->truncate();
        DB::table('league_member_rosters')->truncate();
        DB::table('league_matchups')->truncate();
        DB::table('drafts')->truncate();
        DB::table('draft_picks')->truncate();
        DB::table('draft_rankings')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
