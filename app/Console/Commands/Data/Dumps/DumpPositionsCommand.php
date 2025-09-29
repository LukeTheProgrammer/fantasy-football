<?php

namespace App\Console\Commands\Data\Dumps;

use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class DumpPositionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:dump:positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dumps position data to a JSON file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [];

        $query = Position::query()->select(['*']);

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->each(function ($position) use (&$data, $bar) {
            $a = Arr::except($position->toArray(), ['created_at', 'updated_at', 'deleted_at']);

            $a['id'] = $position->abbreviation;

            $data[] = json_encode($a);

            $bar->advance();
        });

        $path = database_path('data/positions.json');

        file_put_contents($path, '[' . PHP_EOL . implode(',' . PHP_EOL, $data) . PHP_EOL . ']');

        $bar->finish();
        echo PHP_EOL . PHP_EOL;

        return Command::SUCCESS;
    }
}
