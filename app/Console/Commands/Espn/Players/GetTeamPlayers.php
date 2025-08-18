<?php

namespace App\Console\Commands\Espn\Players;

use App\Facades\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GetTeamPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:team-players:get {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads player data from the ESPN API.';

    /**
     * Array of player data.
     *
     * @var array
     */
    protected array $players = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teamId = $this->argument('team_id');

        $this->getPlayers($teamId, 1);

        $this->writeToFile();
    }

    protected function getPlayers(int $teamId, int $pageIndex)
    {
        $data = Espn::getTeamPlayers($teamId, $pageIndex);

        // $count = Arr::get($data, 'count', 0);
        // $pageIndex = Arr::get($data, 'pageIndex', 0);
        $pageSize = Arr::get($data, 'pageSize', 0);
        $pageCount = Arr::get($data, 'pageCount', 0);

        $this->info('Pulling players for team ' . $teamId . ' page ' . $pageIndex . ' / ' . $pageCount);

        $bar = $this->output->createProgressBar($pageSize);

        $bar->start();

        foreach (Arr::get($data, 'items', []) as $playerRef) {
            $playerId = $this->getPlayerId(Arr::get($playerRef, '$ref'));
            $player = Espn::getPlayer($playerId);
            unset($player['$ref']);
            $this->players[] = $player;
            $bar->advance();
        }

        $bar->finish();
        echo PHP_EOL;

        if ($pageIndex < $pageCount) {
            $this->getPlayers($teamId, $pageIndex + 1);
        }
    }

    protected function getPlayerId(string $ref)
    {
        $parts = collect(explode('/', $ref));

        $id = strstr($parts->last(), '?', true);

        return $id;
    }

    protected function writeToFile()
    {
        $path = database_path('data/espn-team-' . $this->argument('team_id') . '-players.json');

        usort($this->players, function ($item1, $item2) {
            return $item1['id'] <=> $item2['id'];
        });

        $playersJson = array_map('json_encode', $this->players);

        $file = '[' . PHP_EOL . implode(',' . PHP_EOL, $playersJson) . PHP_EOL . ']';

        $bytes = file_put_contents($path, $file);

        $this->info(PHP_EOL . "Player data saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
