<?php

namespace App\Console\Commands\Espn\Teams;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class CompareTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:teams:compare';

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
        $teams = Team::all();

        $rows = [];

        $bar = $this->output->createProgressBar($teams->count());

        $teams->each(function ($team) use (&$rows, $bar) {
            $bar->advance();

            $data = Espn::getTeam($team->espn_id);

            $rows[] = [
                $team->id,
                $team->espn_id,
                Arr::get($data, 'id'),
                $team->abbreviation,
                Arr::get($data, 'abbreviation'),
                $team->location,
                Arr::get($data, 'location'),
                $team->name,
                Arr::get($data, 'name'),
                collect(Arr::get($data, 'logos'))->first()['href'],
            ];
        });

        $bar->finish();
        echo PHP_EOL;

        $this->table(
            ['ID', 'Our ESPN ID', 'Their ESPN ID', 'Abb', 'ESPN Abb', 'Loc', 'ESPN Loc', 'Name', 'ESPN Name', 'Logo'],
            $rows
        );
    }
}
