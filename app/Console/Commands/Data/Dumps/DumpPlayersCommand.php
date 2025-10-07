<?php

namespace App\Console\Commands\Data\Dumps;

use App\Models\Player;
use App\Models\PlayerAlias;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class DumpPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:dump:players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dumps player data to a JSON file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->dumpPlayers();
        $this->dumpPlayerAliases();

        return Command::SUCCESS;
    }

    private function dumpPlayers()
    {
        $data = [];

        $query = Player::query()->select(['*']);

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->each(function ($player) use (&$data, $bar) {
            $a = array_merge(
                [ 'ulid' => $player->ulid ],
                Arr::except($player->toArray(), ['id', 'ulid', 'created_at', 'updated_at', 'deleted_at'])
            );

            $data[] = json_encode($a);

            $bar->advance();
        });

        $path = database_path('data/players.json');

        file_put_contents($path, '[' . PHP_EOL . implode(',' . PHP_EOL, $data) . PHP_EOL . ']');

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }

    private function dumpPlayerAliases()
    {
        $data = [];

        $query = PlayerAlias::query()->select(['*']);

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->each(function ($playerAlias) use (&$data, $bar) {
            $pa = array_merge(
                [ 'player_ulid' => $playerAlias->player_ulid ],
                Arr::except($playerAlias->toArray(), ['id', 'player_ulid', 'created_at', 'updated_at', 'deleted_at'])
            );

            $data[] = json_encode($pa);
            $bar->advance();
        });

        $path = database_path('data/player_aliases.json');

        file_put_contents($path, '[' . PHP_EOL . implode(',' . PHP_EOL, $data) . PHP_EOL . ']');

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }
}
