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
            $a = Arr::except($player->toArray(), ['id', 'created_at', 'updated_at', 'deleted_at']);

            $a['position_id'] = $player->position->abbreviation;

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
            $pa = Arr::except($playerAlias->toArray(), ['id', 'created_at', 'updated_at', 'deleted_at']);
            $pa['espn_id'] = $playerAlias->player->espn_id;
            $pa['pfr_id'] = $playerAlias->player->pfr_id;

            $data[] = json_encode($pa);
            $bar->advance();
        });

        $path = database_path('data/player_aliases.json');

        file_put_contents($path, '[' . PHP_EOL . implode(',' . PHP_EOL, $data) . PHP_EOL . ']');

        $bar->finish();
        echo PHP_EOL . PHP_EOL;
    }
}
