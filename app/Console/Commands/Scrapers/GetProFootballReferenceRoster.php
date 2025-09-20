<?php

namespace App\Console\Commands\Scrapers;

use App\Enums\TeamAbb;
use App\Enums\DataSourceEnum;
use App\Models\Player;
use App\Facades\Action;
use App\Facades\Scraper;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class GetProFootballReferenceRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrapers:pfr:get-roster
        { --a|all   : Scrapes all teams }
        { --q|quiet : Scrapes all teams }
        { year?     : Year              }
        { team?     : Team abbreviation }
    ';

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
        $scraper = Scraper::scraper(DataSourceEnum::PRO_FOOTBALL_REFERENCE->value);

        $year = $this->argument('year') ?? select('Year?', [2025, 2024], 2025);

        if ($this->option('all')) {
            foreach (TeamAbb::cases() as $teamAbb) {
                if (! $this->option('quiet')) {
                    $this->info('Pulling rosters for ' . $teamAbb->value);
                }

                $this->processData($scraper->getTeamRoster($teamAbb, $year));
            }
        } else {
            $teamSelection = $this->argument('team') ?? select('Team?', TeamAbb::options());

            $teamAbb = TeamAbb::from($teamSelection);

            if (! $this->option('quiet')) {
                $this->info('Pulling rosters for ' . $teamAbb->value);
            }

            $data = $scraper->getTeamRoster($teamAbb, $year);

            $this->processData($data);
        }

        return Command::SUCCESS;
    }

    private function processData(array $data)
    {
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $player) {
            Action::model(Player::class)->upsert($player);
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }
}
