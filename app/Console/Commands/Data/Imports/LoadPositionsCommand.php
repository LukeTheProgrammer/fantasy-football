<?php

namespace App\Console\Commands\Data\Imports;

use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class LoadPositionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:positions:load';

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
        $path = database_path('data/positions.json');
        $data = file_get_contents($path);
        $positions = json_decode($data, true);

        $bar = $this->output->createProgressBar(count($positions));
        $bar->start();

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['id' => Arr::get($position, 'abbreviation')],
                $position,
            );
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }
}
