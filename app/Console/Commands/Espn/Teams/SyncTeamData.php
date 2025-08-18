<?php

namespace App\Console\Commands\Espn\Teams;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SyncTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:teams:sync';

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

            $team->update([
                'logo' => collect(Arr::get($data, 'logos'))->first()['href'],
            ]);
        });

        $bar->finish();
        echo PHP_EOL;

        $this->info('Team data synced successfully!');
    }
}
