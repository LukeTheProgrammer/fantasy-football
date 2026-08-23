<?php

namespace App\Console\Commands\Espn;

use App\Enums\NFLTeams;
use App\Facades\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class GetRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:get:nfl-rosters
        { --a|all   : Gets all teams }
        { --q|quiet : Gets all teams }
        { season?   : Season         }
        { team?     : NFLTeam Enum   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets NFL rosters from ESPN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->argument('season') ?? select('Season?', [2026, 2025, 2024], 2026);

        if ($this->option('all')) {
            foreach (NFLTeams::cases() as $team) {
                if (! $this->option('quiet')) {
                    $this->info('Pulling rosters for ' . $team->value);
                }

                $this->getRoster($team, $season);
            }

            return Command::SUCCESS;
        }

        $teamSelection = $this->argument('team') ?? select('Team?', NFLTeams::options());

        $team = NFLTeams::from(Str::upper($teamSelection));

        if (! $this->option('quiet')) {
            $this->info('Pulling rosters for ' . $team->value);
        }

        $this->getRoster($team, $season);

        return Command::SUCCESS;
    }

    private function getRoster(NFLTeams $team, int $season): array
    {
        return Espn::nflTeam()->getRoster($team);
    }
}
