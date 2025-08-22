<?php

namespace App\Console\Commands\Espn\Players;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoadAllTeamPlayersFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:load:all-team-players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads player data from a file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::all();

        Schema::disableForeignKeyConstraints();
        DB::table('players')->truncate();
        Schema::enableForeignKeyConstraints();

        $teams->each(function ($team) {
            $this->info('Loading players for team ' . $team->espn_id);
            $this->call('espn:load:team-players:file', ['team_id' => $team->espn_id]);
        });
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

        foreach ($players as $i => $player) {
            $pos = $this->getPosition(Arr::get($player, 'position'));

            Player::updateOrCreate(['espn_id' => Arr::get($player, 'id')], [
                'position_id' => $pos->id,
                'first_name'  => Arr::get($player, 'firstName'),
                'last_name'   => Arr::get($player, 'lastName'),
                'height'      => Arr::get($player, 'height'),
                'weight'      => Arr::get($player, 'weight'),
                'college'     => Arr::get($player, 'college.name'),
                'draft_year'  => Arr::get($player, 'draft.year'),
                'draft_round' => Arr::get($player, 'draft.round'),
                'draft_pick'  => Arr::get($player, 'draft.selection'),
                'draft_team'  => Arr::get($player, 'draft.team.name'),
                'birth_date'  => Carbon::parse(Arr::get($player, 'dateOfBirth'))->toDateTimeString(),
                'headshot'    => Arr::get($player, 'headshot.href'),
            ]);

            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;
    }
}
