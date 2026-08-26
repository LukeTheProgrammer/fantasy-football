<?php

namespace App\Console\Commands\Espn\NFL;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LoadTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:load:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a file.';

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
        $path = storage_path('data/espn/nfl/teams.json');

        if (!file_exists($path)) {
            $this->error('Roster file does not exist: ' . $path);
            $this->call('espn:nfl:get:teams');
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        $teams = Arr::get($data, 'sports.0.leagues.0.teams', []);

        $bar = $this->output->createProgressBar(count($teams));
        $bar->start();

        foreach ($teams as $i => $team) {
            $bar->advance();
            $team = $this->upsert(Arr::get($team, 'team'));

            Player::espnId($team->espn_id)->update([
                'headshot' => $team->logo,
            ]);
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }

    protected function upsert(?array $data = null): ?Team
    {
        if (!$data) {
            return null;
        }

        $logos = Arr::get($data, 'logos', []);

        return Team::updateOrCreate(
            ['id' => Arr::get($data, 'abbreviation')],
            [
                'espn_id'      => Arr::get($data, 'id'),
                'abbreviation' => Arr::get($data, 'abbreviation'),
                'location'     => Arr::get($data, 'location'),
                'name'         => Arr::get($data, 'name'),
                'logo'         => Arr::get($logos, '0.href'),
            ],
        );
    }
}
