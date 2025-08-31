<?php

namespace App\Console\Commands\Espn\Rosters;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LoadRosterFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:roster:load {espn_team_id}';

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
     * NFL Teams.
     *
     * @var Collection|null
     */
    protected ?Collection $teams = null;

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
        $espnTeamId = $this->argument('espn_team_id');

        $this->team = Team::where('espn_id', '=', $espnTeamId)->first();

        $this->loadPositions();
        $this->loadTeams();

        $this->info('Loading players for ' . $this->team->abbreviation . ' [' . $espnTeamId . ']');

        $path = database_path('data/ESPN/rosters/espn-team-' . $espnTeamId . '-roster.json');

        if (!file_exists($path)) {
            $this->error('Roster file does not exist: ' . $path);
            $this->call('espn:roster:get', ['team_id' => $espnTeamId]);
        }

        $this->loadRoster($path);
    }

    protected function loadTeams()
    {
        $this->teams = Team::all()->keyBy('espn_id');
    }

    protected function getTeam(int $teamId)
    {
        return $this->teams->get($teamId);
    }

    protected function loadPositions()
    {
        $this->positions = Position::all()->keyBy('abbreviation');
    }

    protected function getPosition(string $abbreviation)
    {
        return $this->positions->get($abbreviation);
    }

    protected function loadRoster(string $path)
    {
        $data = file_get_contents($path);

        $roster = json_decode($data, true);
        // $pc = count($roster);

        $bar = $this->output->createProgressBar(1);
        $bar->start();

        $skip = [
            // 'defense',
            // 'specialTeam',
            // 'practiceSquad',
        ];

        foreach (Arr::get($roster, 'athletes', []) as $i => $positions) {
            if (in_array($positions['position'], $skip)) {
                continue;
            }

            foreach (Arr::get($positions, 'items', []) as $ii => $player) {
                $pos = $this->getPosition(Arr::get($player, 'position.abbreviation'));

                if (! $pos instanceof Position) {
                    continue;
                }

                $this->upsert($player, $pos);
                $bar->advance();
            }
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
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
            // 'draft_year'    => Arr::get($data, 'draft.year'),
            // 'draft_round'   => Arr::get($data, 'draft.round'),
            // 'draft_pick'    => Arr::get($data, 'draft.selection'),
            // 'draft_team'    => Arr::get($data, 'draft.team.name'),
            'birth_date'    => Carbon::parse(Arr::get($data, 'dateOfBirth'))->toDateTimeString(),
            'headshot'      => Arr::get($data, 'headshot.href'),
        ]);

        return $player;
    }
}
