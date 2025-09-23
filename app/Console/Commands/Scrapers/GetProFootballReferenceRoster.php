<?php

namespace App\Console\Commands\Scrapers;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Enums\DataSourceEnum;
use App\Enums\TeamAbb;
use App\Exceptions\AmbiguousPlayerException;
use App\Facades\Action;
use App\Facades\Scraper;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Services\Scrapers\Resources\ProFootballReference;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class GetProFootballReferenceRoster extends Command
{
    use DisambiguatesPlayers;

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

    protected ?ProFootballReference $scraper = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->scraper = Scraper::scraper(DataSourceEnum::PRO_FOOTBALL_REFERENCE->value);

        $year = $this->argument('year') ?? select('Year?', [2025, 2024], 2025);

        if ($this->option('all')) {
            foreach (TeamAbb::cases() as $teamAbb) {
                if (! $this->option('quiet')) {
                    $this->info('Pulling rosters for ' . $teamAbb->value);
                }

                $roster = $this->getData($year, $teamAbb->value);

                if (empty($roster)) {
                    $this->error('No data found for ' . $teamAbb->value);
                    continue;
                }

                $this->processData($roster);

                $this->saveData($roster, $year, $teamAbb->value);
            }
        } else {
            $teamSelection = $this->argument('team') ?? select('Team?', TeamAbb::options());

            $teamAbb = TeamAbb::from(Str::upper($teamSelection));

            if (! $this->option('quiet')) {
                $this->info('Pulling rosters for ' . $teamAbb->value);
            }

            $data = $this->getData($year, $teamAbb->value);

            if (! empty($data)) {
                $this->processData($data);
                $this->saveData($data, $year, $teamAbb->value);
            }
        }

        return Command::SUCCESS;
    }

    private function getData(int $year, string $team)
    {
        $path = $this->getFilePath($year, $team);
        $this->info($path);

        if (file_exists($path)) {
            $json = file_get_contents($path);
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to decode JSON: ' . json_last_error_msg());
                dd($path);
            }

            if (! empty($data)) {
                return $data;
            }
        }

        dd('die');

        return $this->scraper->getTeamRoster(TeamAbb::from($team), $year);
    }

    private function processData(array $data)
    {
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $playerData) {
            try {
                $player = Action::model(Player::class)->upsert($playerData);
            } catch (AmbiguousPlayerException $e) {
                if ($this->option('quiet')) {
                    report($e);
                    continue;
                }

                $player = $this->handleAmbiguousPlayer($playerData);

                if ($player instanceof Player) {
                    Action::model(Player::class)->update($player, $playerData);
                }
            }
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }

    private function saveData(array $data, int $year, string $team)
    {
        $path = $this->getFilePath($year, $team);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getFilePath(int $year, string $team): string
    {
        return storage_path('data/pro-football-reference/rosters-' . $year . '-' . $team . '.json');
    }

    private function handleAmbiguousPlayer(array $data)
    {
        $player = $this->disambiguatePlayer(
            Arr::get($data, 'full_name'),
            Position::find(Arr::get($data, 'position_id')),
            Team::find(Arr::get($data, 'team_id')),
        );

        if (! $player instanceof Player) {
            $this->info('Player not found ' . $data['full_name']);

            if (confirm('Create player?')) {
                $player = Action::model(Player::class)->create($data);
            }
        }

        return $player;
    }
}
