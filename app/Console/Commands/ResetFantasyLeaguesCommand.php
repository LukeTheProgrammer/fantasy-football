<?php

namespace App\Console\Commands;

use App\Models\DraftRanking;
use App\Models\League;
use Illuminate\Console\Command;

class ResetFantasyLeaguesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-fantasy-leagues
        { season? : The season to rebuild, defaults to the current year }
        { --all : Wipe every season rather than just the one being rebuilt }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds the fantasy leagues for a season from the ESPN API.';

    /**
     * The ESPN leagues to rebuild, keyed by name.
     *
     * @var array<string, int>
     */
    private array $leagues = [
        'Milhaven'  => 691509,
        'HawkHorns' => 61235367,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $season = (int) ($this->argument('season') ?? date('Y'));

        $this->rebuildData($season);

        foreach ($this->leagues as $name => $platformId) {
            $this->info("Importing {$name} ({$platformId}) for {$season}");

            $this->call('import:fantasy:league', [
                '--create'      => true,
                '--creator-id'  => 1,
                '--season'      => $season,
                '--platform'    => 'ESPN',
                '--platform-id' => $platformId,
                '--espn-s2'     => config('services.espn.default_s2'),
                '--espn-swid'   => config('services.espn.default_swid'),
            ]);

            $league = League::query()
                ->where('platform_id', $platformId)
                ->where('season', $season)
                ->first();

            if (!$league instanceof League) {
                $this->error("Import did not create {$name} for {$season}, skipping its roster.");

                continue;
            }

            $this->call('import:fantasy:roster', [
                'leagueId' => $league->id,
                'season'   => $season,
            ]);
        }

        return Command::SUCCESS;
    }

    /**
     * League deletes cascade to settings, members, rosters, matchups, drafts
     * and picks, so only the leagues and the player-scoped rankings are
     * removed here. The delete must be forced: League soft deletes, and a
     * soft delete neither fires the cascade nor frees the unique slug.
     */
    private function rebuildData(int $season): void
    {
        $leagues = League::withTrashed();
        $rankings = DraftRanking::query();

        if (!$this->option('all')) {
            $leagues->where('season', $season);
            $rankings->where('season', $season);
        }

        $count = $leagues->count();

        if ($count > 0 && !$this->confirm("Delete {$count} league(s) and their drafts, rosters and matchups?", true)) {
            return;
        }

        $leagues->forceDelete();
        $rankings->delete();
    }
}
