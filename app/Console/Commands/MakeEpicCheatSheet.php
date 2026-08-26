<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use function Laravel\Prompts\select;

class MakeEpicCheatSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epic-cheat-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a JSON file into the database';

    protected array $data = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->loadDyansty();
        $this->loadRedraft();
        $this->handleKeepers();
        $this->writeFile();
        $this->info('Epic Cheat Sheet created successfully!');
    }

    public function newPlayer(array $player)
    {
        return [
            'Available' => 'Y',
            'Player'    => Arr::get($player, 'PLAYER NAME'),
            'Team'      => Arr::get($player, 'TEAM'),
            'Pos'       => preg_replace('/\d/', '', Arr::get($player, 'POS')),
            'D Rank'    => '',
            'D Pos'     => '',
            'R Rank'    => '',
            'R Pos'     => '',
            'keeper'    => '',
        ];
    }

    public function loadDyansty()
    {
        $this->info('Loading Dynasty data');

        $path = database_path('data/rankings/FantasyPros/2025-Dynasty-Half-PPR.csv');
        $fp = fopen($path, 'r');

        $bar = $this->output->createProgressBar(1);
        $bar->start();

        $headers = [];

        while (($line = fgetcsv($fp)) !== false) {
            if (empty($headers)) {
                $headers = $line;
            } else {
                $player = array_combine($headers, $line);
                $pn = Arr::get($player, 'PLAYER NAME');

                if (!isset($this->data[$pn])) {
                    $this->data[$pn] = $this->newPlayer($player);
                }

                $this->data[$pn]['D Rank'] = Arr::get($player, 'RK');
                $this->data[$pn]['D Pos'] = Arr::get($player, 'POS');
            }
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;

        fclose($fp);
    }

    public function loadRedraft()
    {
        $this->info('Loading Dynasty data');

        $path = database_path('data/rankings/FantasyPros/2025-Redraft-Half-PPR.csv');
        $fp = fopen($path, 'r');

        $bar = $this->output->createProgressBar(1);
        $bar->start();

        $headers = [];

        while (($line = fgetcsv($fp)) !== false) {
            if (empty($headers)) {
                $headers = $line;
            } else {
                $player = array_combine($headers, $line);
                $pn = Arr::get($player, 'PLAYER NAME');

                if (!isset($this->data[$pn])) {
                    $this->data[$pn] = $this->newPlayer($player);
                }

                $this->data[$pn]['R Rank'] = Arr::get($player, 'RK');
                $this->data[$pn]['R Pos'] = Arr::get($player, 'POS');
            }
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;

        fclose($fp);
    }

    public function handleKeepers()
    {
        $c = 0;
        $path = database_path('data/Epic-Keepers.csv');
        $fp = fopen($path, 'r');

        while (($line = fgetcsv($fp)) !== false) {
            foreach ($line as $i => $name) {
                if ($i === 0) {
                    $this->info('Processing ' . $name);

                    continue;
                }

                $c++;

                $candidates = [
                    'none' => 'none',
                ];

                foreach (explode(' ', $name) as $part) {
                    if (strlen($part) < 3) {
                        continue;
                    }

                    foreach ($this->data as $player) {
                        if (Arr::get($player, 'Available') === 'N') {
                            continue;
                        }

                        $pname = Arr::get($player, 'Player');
                        $pos = Arr::get($player, 'Pos');
                        $team = Arr::get($player, 'Team');

                        if (isset($candidates[$pname])) {
                            continue;
                        }

                        if (str_contains($pname, $part)) {
                            $candidates[$pname] = $pname . ' ' . $pos . ' ' . $team;
                        }
                    }
                }

                if (count($candidates) > 1) {
                    $selection = select('Which Player matches ' . $name, $candidates);
                } else {
                    $selection = array_key_first($candidates);
                }

                if ($selection === 'none') {
                    $this->error('No player found for ' . $name);

                    continue;
                }

                $this->data[$selection]['Available'] = 'N';
                $this->data[$selection]['keeper'] = $name;
            }

            $this->info("Processed $c players");
        }

        fclose($fp);
    }

    public function writeFile()
    {
        $path = database_path('data/Epic-Cheat-Sheet.csv');
        $fp = fopen($path, 'w');

        $bar = $this->output->createProgressBar(count($this->data));
        $bar->start();

        $headers = false;

        foreach ($this->data as $player) {
            if (!$headers) {
                // fputcsv($fp, array_keys($player));
                $headers = true;
            }

            fputcsv($fp, array_values($player));
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;

        fclose($fp);
    }
}
