<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoadTeamsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a JSON file into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonFile = database_path('data/teams.json');
        $jsonData = file_get_contents($jsonFile);
        $teams = json_decode($jsonData, true);

        Schema::disableForeignKeyConstraints();
        DB::table('teams')->truncate();
        Schema::enableForeignKeyConstraints();

        $bar = $this->output->createProgressBar(count($teams));

        foreach ($teams as $team) {
            Team::create($team);
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;
        $this->info('Team data loaded successfully!');
    }
}
