<?php

namespace App\Console\Commands\Espn\Players;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;

class LoadTeamPlayersFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:load:team-players:file {espn_team_id}';

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

        $path = database_path('data/ESPN/players/espn-team-' . $espnTeamId . '-players.json');

        if (!file_exists($path)) {
            $this->error('Player file does not exist: ' . $path);
            $this->call('espn:team-players:get', ['team_id' => $espnTeamId]);
        }

        $this->loadPlayers($path);
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

    protected function loadPlayers(string $path)
    {
        $data = file_get_contents($path);

        $players = json_decode($data, true);
        $pc = count($players);

        // $bar = $this->output->createProgressBar(count($players));
        // $bar->start();

        foreach ($players as $i => $player) {
            $this->info('Player ' . ($i + 1) . ' of ' . $pc);
            $team = $this->getTeam(Arr::get($player, 'team_id'));

            if (! $team instanceof Team) {
                continue;
            }

            $pos = $this->getPosition(Arr::get($player, 'position'));

            if (! $pos instanceof Position) {
                continue;
            }

            $this->upsert($player, $team, $pos);

            // $bar->advance();
        }

        // $bar->finish();
        // echo PHP_EOL . PHP_EOL;
    }

    protected function upsert(array $data, Team $team, Position $position): Player
    {
        $this->table(
            ['ESPN ID', 'Position', 'Team', 'First Name', 'Last Name', 'Full Name'],
            [
                [
                    Arr::get($data, 'id'),
                    $position->abbreviation,
                    $team->abbreviation,
                    Arr::get($data, 'firstName'),
                    Arr::get($data, 'lastName'),
                    Arr::get($data, 'fullName'),
                ]
            ]
        );

        if (confirm('Upsert player?')) {
            $player = Action::model(Player::class)->upsert([
                'espn_id'       => Arr::get($data, 'id'),
                'position_id'   => $position->id,
                'team_id'       => $team->id,
                'first_name'    => Arr::get($data, 'firstName'),
                'last_name'     => Arr::get($data, 'lastName'),
                'full_name'     => Arr::get($data, 'fullName'),
                'jersey_number' => Arr::get($data, 'jersey'),
                'height'        => Arr::get($data, 'height'),
                'weight'        => Arr::get($data, 'weight'),
                'draft_year'    => Arr::get($data, 'draft.year'),
                'draft_round'   => Arr::get($data, 'draft.round'),
                'draft_pick'    => Arr::get($data, 'draft.selection'),
                'draft_team'    => Arr::get($data, 'draft.team.name'),
                'birth_date'    => Carbon::parse(Arr::get($data, 'dateOfBirth'))->toDateTimeString(),
                'headshot'      => Arr::get($data, 'headshot.href'),
            ]);
        } else {
            dd('fuck');
        }

        return $player;
    }
}
