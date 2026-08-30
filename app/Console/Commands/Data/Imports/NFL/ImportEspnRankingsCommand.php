<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Facades\Espn;
use App\Facades\Import;
use App\Models\League;
use App\Traits\ReportsImportErrors;
use Illuminate\Console\Command;

class ImportEspnRankingsCommand extends Command
{
    use ReportsImportErrors;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:rankings:import
        { --league-id= : The league whose credentials to read ESPN with }
        { --season=    : Season to import, defaults to the league season }
        { --force      : Import again even when today is already stored }
        { --errors     : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import ESPN's draft board: average auction value and average draft position per player";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $league = $this->league();

        if (!$league instanceof League) {
            $this->error('No league with ESPN credentials to read with.');

            return self::FAILURE;
        }

        $season = (int) ($this->option('season') ?: $league->season_id);
        $importer = Import::espnRankings();
        $today = now()->toDateString();

        // A board moves day to day, not hour to hour, so a second run on the
        // same day is work nobody asked for.
        if (!$this->option('force') && $importer->capturedOn($season, $today)) {
            $this->info($season . ' ESPN board already captured on ' . $today . '.');

            return self::SUCCESS;
        }

        $this->info('Importing ' . $season . ' ESPN draft board');

        $players = Espn::forcePull(true)->getFantasyPlayers($league->credentials, $season);

        $result = $importer->import($season, $players, $today);

        $this->table(
            ['Created', 'Updated', 'Skipped'],
            [[$result['created'], $result['updated'], $result['skipped']]]
        );

        $this->reportErrors($importer, $result);

        // Stored unadjusted, so the board must read them for what they are.
        if ($league->settings?->two_qb) {
            $this->warn('This league starts two quarterbacks; ESPN aggregates single quarterback leagues and will read low at the position.');
        }

        return self::SUCCESS;
    }

    /**
     * The league whose ESPN credentials the pull is made with. Any league will
     * do — the player pool is the platform's, not one league's.
     */
    private function league(): ?League
    {
        if ($leagueId = $this->option('league-id')) {
            return League::find($leagueId);
        }

        return League::query()
            ->whereNotNull('credentials')
            ->orderByDesc('season_id')
            ->first();
    }
}
