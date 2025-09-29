<?php

namespace App\Console\Commands\Data\Dumps;

use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class DumpTeamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:dump:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dumps team data to a JSON file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [];

        $query = Team::query()->select(['*']);

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->each(function ($team) use (&$data, $bar) {
            $a = Arr::except($team->toArray(), ['created_at', 'updated_at', 'deleted_at']);

            $data[] = json_encode($a);

            $bar->advance();
        });

        $path = database_path('data/teams.json');

        file_put_contents($path, '[' . PHP_EOL . implode(',' . PHP_EOL, $data) . PHP_EOL . ']');

        $bar->finish();
        echo PHP_EOL . PHP_EOL;

        return Command::SUCCESS;
    }
}
