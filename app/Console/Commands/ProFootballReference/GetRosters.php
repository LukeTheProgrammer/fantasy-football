<?php

namespace App\Console\Commands\ProFootballReference;

use App\Enums\NFLTeams;
use App\Facades\ProFootballReference;
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
    protected $signature = 'pfr:get:rosters
        { --a|all   : Scrapes all teams }
        { --q|quiet : Scrapes all teams }
        { year?     : Year              }
        { team?     : NFLTeam Enum      }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets rosters from Pro Football Reference';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? select('Year?', [2025, 2024], 2025);

        if ($this->option('all')) {
            foreach (NFLTeams::cases() as $team) {
                if (! $this->option('quiet')) {
                    $this->info('Pulling rosters for ' . $team->value);
                }

                $this->getRoster($team, $year);
            }

            return Command::SUCCESS;
        }

        $teamSelection = $this->argument('team') ?? select('Team?', NFLTeams::options());

        $team = NFLTeams::from(Str::upper($teamSelection));

        if (! $this->option('quiet')) {
            $this->info('Pulling rosters for ' . $team->value);
        }

        $this->getRoster($team, $year);

        return Command::SUCCESS;
    }

    private function getRoster(NFLTeams $team, int $year): array
    {
        return ProFootballReference::getTeamRoster($team, $year);
    }
}
