<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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

        $this->call('import:fantasy-nfl:league');
        // TODO - Clean up these commands, make ESPN League and Roster Drivers
        // $this->call('espn:ffl:get:rosters', ['leagueId' => 1, 'year' => 2025]);
        // $this->call('import:fantasy-nfl:points', ['--quiet' => true, 'leagueId' => 1, 'year' => 2025]);

        return Command::SUCCESS;
    }
}
