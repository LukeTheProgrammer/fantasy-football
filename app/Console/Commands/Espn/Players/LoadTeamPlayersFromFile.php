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

        $this->info('Loading players for ' . $this->team->abbreviation . ' [' . $espnTeamId . ']');

        $path = database_path('data/ESPN/players/espn-team-' . $espnTeamId . '-players.json');

        if (!file_exists($path)) {
            $this->error('Player file does not exist: ' . $path);
            $this->call('espn:team-players:get', ['team_id' => $espnTeamId]);
        }

        $this->loadPlayers($path);
    }

    protected function loadPositions()
    {
        $this->positions = Position::all()->keyBy('abbreviation');
    }

    protected function getPosition(array $position)
    {
        $abbreviation = Arr::get($position, 'abbreviation');
        $name = Arr::get($position, 'name');

        $pos = $this->positions->get($abbreviation);

        if ($pos instanceof Position) {
            return $pos;
        }

        $pos = Position::create([
            'abbreviation' => $abbreviation,
            'name'         => $name,
        ]);

        $this->loadPositions();

        return $pos;
    }

    protected function loadPlayers(string $path)
    {
        $data = file_get_contents($path);

        $players = json_decode($data, true);

        $bar = $this->output->createProgressBar(count($players));
        $bar->start();

        // $stats = [];

        foreach ($players as $i => $player) {
            $pos = $this->getPosition(Arr::get($player, 'position'));
            // $this->info(Arr::get($player, 'id') . ' ' . $status);
            // $status = Arr::get($player, 'status.name');

            // if (!in_array($status, $stats)) {
            //     $stats[] = $status;
            // }

            Action::model(Player::class)->upsert([
                'espn_id'       => Arr::get($player, 'id'),
                'position_id'   => $pos->id,
                'team_id'       => $this->team->id,
                'first_name'    => Arr::get($player, 'firstName'),
                'last_name'     => Arr::get($player, 'lastName'),
                'full_name'     => Arr::get($player, 'fullName'),
                'jersey_number' => Arr::get($player, 'jersey'),
                'height'        => Arr::get($player, 'height'),
                'weight'        => Arr::get($player, 'weight'),
                'college'       => Arr::get($player, 'college.name'),
                'draft_year'    => Arr::get($player, 'draft.year'),
                'draft_round'   => Arr::get($player, 'draft.round'),
                'draft_pick'    => Arr::get($player, 'draft.selection'),
                'draft_team'    => Arr::get($player, 'draft.team.name'),
                'birth_date'    => Carbon::parse(Arr::get($player, 'dateOfBirth'))->toDateTimeString(),
                'headshot'      => Arr::get($player, 'headshot.href'),
            ]);

            $bar->advance();
        }

        // $this->info('Stats: ' . implode(', ', $stats));

        $bar->finish();
        echo PHP_EOL;
    }
}
