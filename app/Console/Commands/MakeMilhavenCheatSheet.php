<?php

namespace App\Console\Commands;

use App\Models\DraftRanking;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class MakeMilhavenCheatSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'milhaven-cheat-sheet';

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
        $this->loadData();
        $this->writeFile();
        $this->info('Milhaven Cheat Sheet created successfully!');
    }

    public function newItem()
    {
        return [
            'rank' => null,
            'player' => null,
            'team' => null,
            'pos' => null,
            'tier' => null,
            'posRk' => null,
            'adv' => null,
            'actual' => 0,
            'value' => 0,
        ];
    }

    public function updateItem($id, $key, $value)
    {
        $old = Arr::get($this->data[$id], $key);

        if (empty($old) && empty($value)) {
            return;
        }

        if (empty($old)) {
            $this->data[$id][$key] = $value;

        } elseif ($key === 'rank' || $key === 'tier') {
            $this->data[$id][$key] = ($old < $value) ? $old : $value;

        } else {
            $this->data[$id][$key] = ($old > $value) ? $old : $value;
        }
    }

    public function loadData()
    {
        $this->info('Loading data');

        $q = DraftRanking::forSeason(2025)->orderBy('id');

        $bar = $this->output->createProgressBar($q->count());
        $bar->start();

        $q->lazy()->each(function ($ranking) use ($bar) {
            $pid = $ranking->player_id;

            if (! isset($this->data[$pid])) {
                $this->data[$pid] = $this->newItem();
                $this->data[$pid]['player'] = $ranking->player->full_name;
                $this->data[$pid]['team'] = $ranking->player->team->abbreviation;
                $this->data[$pid]['pos'] = $ranking->player->position->abbreviation;
            }

            foreach ($this->data[$pid] as $key => $value) {
                $this->updateItem($pid, $key, $value);
            }

            $this->updateItem($pid, 'rank', $ranking->rank);
            $this->updateItem($pid, 'tier', $ranking->tier);
            $this->updateItem($pid, 'adv', $ranking->adv);

            $bar->advance();
        });

        $bar->finish();
        echo PHP_EOL;
    }

    public function writeFile()
    {
        $path = database_path('data/Milhaven-Cheat-Sheet.csv');
        $fp = fopen($path, 'w');

        $bar = $this->output->createProgressBar(count($this->data));
        $bar->start();

        $headers = false;

        foreach ($this->data as $player) {
            if (! $headers) {
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
