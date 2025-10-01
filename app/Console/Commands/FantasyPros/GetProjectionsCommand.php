<?php

namespace App\Console\Commands\FantasyPros;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Facades\FantasyPros;
use App\Services\FantasyPros\Resources\ProjectionsResource;
use Illuminate\Console\Command;

class GetProjectionsCommand extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:projections:get';

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
        $fp = FantasyPros::projections();

        $sources = array_keys($fp->sources);

        $bar = $this->output->createProgressBar(count($sources));
        $bar->start();

        foreach ($sources as $label) {
            $fp->getProjections($label);
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;
    }
}
