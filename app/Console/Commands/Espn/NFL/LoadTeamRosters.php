<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LoadTeamRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:load:team-rosters
        { --a|all       : Load all rosters   }
        { --q|quiet     : Do not show output }
        { espn_team_id? : The ESPN Team ID   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads player data from a file.';

    /**
     * NFL Positions.
     *
     * @var Collection|null
     */
    protected ?Collection $positions = null;

    /**
     * The Team.
     *
     * @var Team|null
     */
    protected ?Team $team = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        if ($this->option('all')) {
            return $this->loadAllRosters();
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            return $this->loadTeamRoster($teamId);
        }
    }

    protected function setUp()
    {
        $this->positions = Position::all()->keyBy('abbreviation');
    }

    protected function loadAllRosters()
    {
        Team::noFA()->get()->each(fn (Team $team) => $this->loadTeamRoster($team));
    }

    protected function loadTeamRoster(int|string|Team $teamArg)
    {
        $this->team = ($teamArg instanceof Team) ? $teamArg : Team::where('espn_id', '=', $teamArg)->first();

        $teamId = $this->team->espn_id;

        if (! $this->option('quiet')) {
            $this->info('Loading players for ' . $this->team->id . ' [' . $teamId . ']' . PHP_EOL);
        }

        $path = storage_path('data/espn/nfl/team-rosters/team-roster-' . $teamId . '.json');

        if (! file_exists($path)) {
            $this->error('Roster file does not exist: ' . $path);
            $this->call('espn:nfl:get:team-rosters', ['espn_team_id' => $teamId]);
        }

        $this->loadRoster($path);
    }

    protected function loadRoster(string $path)
    {
        $data = file_get_contents($path);

        $roster = json_decode($data, true);

        if (! $this->option('quiet')) {
            $bar = $this->output->createProgressBar(1);
            $bar->start();
        }

        foreach (Arr::get($roster, 'athletes', []) as $i => $positions) {
            foreach (Arr::get($positions, 'items', []) as $ii => $player) {
                $pos = $this->positions->get(Arr::get($player, 'position.abbreviation'));

                if (! $pos instanceof Position) {
                    continue;
                }

                $this->upsert($player, $pos);
                if (! $this->option('quiet')) {
                    $bar->advance();
                }
            }
        }

        if (! $this->option('quiet')) {
            $bar->finish();
            echo PHP_EOL . PHP_EOL;
        }
    }

    protected function upsert(array $data, Position $position): Player
    {
        $player = Action::model(Player::class)->upsert([
            'espn_id'       => Arr::get($data, 'id'),
            'position_id'   => $position->id,
            'team_id'       => $this->team->id,
            'first_name'    => Arr::get($data, 'firstName'),
            'last_name'     => Arr::get($data, 'lastName'),
            'full_name'     => Arr::get($data, 'fullName'),
            'jersey_number' => Arr::get($data, 'jersey'),
            'height'        => Arr::get($data, 'height'),
            'weight'        => Arr::get($data, 'weight'),
            'birth_date'    => Carbon::parse(Arr::get($data, 'dateOfBirth'))->toDateTimeString(),
            'headshot'      => Arr::get($data, 'headshot.href'),
        ]);

        return $player;
    }
}
