<?php

namespace App\Console\Commands\ProFootballReference;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Facades\ProFootballReference;
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
    protected $signature = 'pfr:load:rosters
        { --a|all   : Scrapes all teams }
        { --q|quiet : Scrapes all teams }
        { season?   : Season            }
        { team?     : Team abbreviation }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads rosters from Pro Football Reference';

    private ?int $season = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->season = $this->argument('season') ?? select('Season?', [2026, 2025, 2024], 2026);

        if ($this->option('all')) {
            Team::noFA()->get()->each(fn (Team $team) => $this->loadRoster($team));

            return Command::SUCCESS;
        }

        $teamSelection = $this->argument('team') ?? select('Team?', NFLTeams::options());

        $team = Team::find(Str::upper($teamSelection));

        $this->loadRoster($team);

        return Command::SUCCESS;
    }

    private function loadRoster(Team $team)
    {
        if (! $this->option('quiet')) {
            $this->info('Pulling rosters for ' . $team->id);
        }

        $roster = ProFootballReference::getRoster($team, $this->season);

        foreach ($roster as $player) {
            $this->loadPlayer($player, $team);
        }
    }

    private function loadPlayer(array $playerData, Team $team)
    {
        $player = $this->findPlayer($playerData, $team);

        if (! $player instanceof Player) {
            return;
        }

        Action::model(PlayerTeam::class)->upsert($player, $team);
    }

    private function findPlayer(array $playerData, Team $team): ?Player
    {
        $player = Player::pfrId($playerData['pfr_id'])->first();

        if ($player instanceof Player) {
            return $player;
        }

        $player = $this->disambiguatePlayer(
            Arr::get($playerData, 'full_name'),
            Position::find(Arr::get($playerData, 'position_id')),
            $team,
        );

        if ($player instanceof Player) {
            return $player;
        }

        return null;
    }
}
