<?php

namespace App\Console\Commands\Data;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use App\Models\Team;

class LoadTeamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:teams:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = database_path('data/teams.json');
        $data = file_get_contents($path);
        $teams = json_decode($data, true);

        $bar = $this->output->createProgressBar(count($teams));
        $bar->start();

        foreach ($teams as $team) {
            Team::updateOrCreate(
                [ 'espn_id' => Arr::get($team, 'espn_id') ],
                $team,
            );
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }
}
