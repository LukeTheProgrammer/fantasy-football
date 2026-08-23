<?php

namespace App\Console\Commands\Espn;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Facades\Espn;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class LoadRosters extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:load:nfl-rosters
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
    protected $description = 'Loads NFL rosters from ESPN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->argument('season') ?? select('Season?', [2026, 2025, 2024], 2026);

        if ($this->option('all')) {
            Team::noFA()->get()->each(
                fn (Team $team) => $this->loadRoster($team, $season)
            );

            return Command::SUCCESS;
        }

        $teamSelection = $this->argument('team') ?? select('Team?', NFLTeams::options());

        $teamId = NFLTeams::from(Str::upper($teamSelection));

        $team = Team::forAbbreviation($teamId)->first();

        if (! $this->option('quiet')) {
            $this->info('Pulling rosters for ' . $team->value);
        }

        $this->loadRoster($team, $season);

        return Command::SUCCESS;
    }

    private function loadRoster(Team $team, int $season)
    {
        $roster = Espn::nflTeam()->getRoster($team);

        foreach ($roster as $player) {
            $playerModel = Player::espnId($player['id'])->first();

            if (! $playerModel instanceof Player) {
                $pos = Position::find(Arr::get($player, 'position'));
                $playerModel = $this->disambiguatePlayer($player['name'], $pos, $team);

                if (! $playerModel instanceof Player) {
                    continue;
                }
            }

            Action::model(PlayerTeam::class)->upsert($playerModel, $team);
        }
    }
}
