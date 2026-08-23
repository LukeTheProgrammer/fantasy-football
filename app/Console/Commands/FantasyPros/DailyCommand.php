<?php

namespace App\Console\Commands\FantasyPros;

use Illuminate\Console\Command;

class DailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:daily
        { --season= : Season to run for, defaults to the current season }
        { --week=   : Week to pull projections for, defaults to the current week }
        { --force   : Pull again even if today was already captured }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull today\'s FantasyPros data, import it, and snapshot the ranking averages';

    /**
     * Execute the console command.
     *
     * Each stage reads what the stage before it left behind, so they run in
     * order and a failed pull leaves the previous capture importable.
     */
    public function handle()
    {
        $season = $this->option('season');
        $week = $this->option('week');

        $seasonOption = $season ? ['--season' => $season] : [];
        $weekOption = $week ? ['--week' => $week] : [];
        $forceOption = $this->option('force') ? ['--force' => true] : [];

        $steps = [
            'fantasy-pros:rankings:get'       => $seasonOption + $forceOption,
            'fantasy-pros:rankings:import'    => $seasonOption,
            'fantasy-pros:projections:get'    => $seasonOption + $weekOption + $forceOption,
            'fantasy-pros:projections:import' => $seasonOption + $weekOption,
            'rankings:calculate-averages'     => $seasonOption,
        ];

        foreach ($steps as $command => $arguments) {
            $this->newLine();
            $this->info('==> ' . $command);

            if ($this->call($command, $arguments) !== self::SUCCESS) {
                $this->error($command . ' failed, stopping.');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('FantasyPros daily run complete.');

        return self::SUCCESS;
    }
}
