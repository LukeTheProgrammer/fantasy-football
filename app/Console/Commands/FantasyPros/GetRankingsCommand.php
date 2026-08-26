<?php

namespace App\Console\Commands\FantasyPros;

use App\Enums\FantasyProsDraftSlate;
use App\Facades\FantasyPros;
use Illuminate\Console\Command;

class GetRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:rankings:get
        { --slate=*  : Limit to one or more draft boards, defaults to all }
        { --season=  : Season to pull, defaults to the current season }
        { --force    : Pull again even if the board was already captured today }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull FantasyPros overall draft boards into the data archive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slates = $this->slates();

        if ($slates === false) {
            return self::FAILURE;
        }

        $rankings = FantasyPros::rankings()->forcePull((bool) $this->option('force'));

        $season = $this->option('season') ? (int) $this->option('season') : null;

        $rows = [];
        $missed = [];

        $bar = $this->output->createProgressBar(count($slates));
        $bar->start();

        foreach ($slates as $slate) {
            $players = $rankings->getRanking($slate, $season);

            $capturedToday = $rankings->capturedToday($slate, $season);

            if (!$capturedToday) {
                $missed[] = $slate->value;
            }

            $rows[] = [
                $slate->value,
                $players === false ? 'no data' : count($players) . ' players',
                $capturedToday ? 'today' : 'STALE',
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Board', 'Rows', 'Capture'], $rows);

        if (!empty($missed)) {
            $this->error('No capture landed today for: ' . implode(', ', $missed));
            $this->line('FantasyPros keeps no archive, so these days cannot be pulled later.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * The boards to pull, all of them unless the command names some.
     *
     * @return array<int, FantasyProsDraftSlate>|false
     */
    private function slates()
    {
        $requested = $this->option('slate');

        if (empty($requested)) {
            return FantasyProsDraftSlate::cases();
        }

        $slates = [];

        foreach ($requested as $value) {
            $slate = FantasyProsDraftSlate::tryFrom($value);

            if (!$slate instanceof FantasyProsDraftSlate) {
                $this->error('Unknown board: ' . $value);
                $this->line('Available: ' . implode(', ', array_keys(FantasyProsDraftSlate::options())));

                return false;
            }

            $slates[] = $slate;
        }

        return $slates;
    }
}
