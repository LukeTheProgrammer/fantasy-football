<?php

namespace App\Console\Commands\FantasyPros;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Facades\FantasyPros;
use App\Services\FantasyPros\Resources\ProjectionsResource;
use Illuminate\Console\Command;

class GetProjectionsCommand extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fantasy-pros:projections:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a JSON file into the database';

    protected ?string $dir = null;

    protected ?ProjectionsResource $fp = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->fp = FantasyPros::projections();

        $this->dir = storage_path('data/fantasy-pros/projections/' . date('Y-m-d'));

        if (! file_exists($this->dir)) {
            mkdir($this->dir, 0775, true);
        }

        foreach ($this->fp->sources as $label => $url) {
            $this->getProjection($label, $url);
        }
    }

    private function getProjection(string $label, string $url)
    {
        $filePath = $this->dir . '/' . $label . '.json';

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $players = json_decode($json, true);

            return $players;
        }

        $players = $this->fp->getProjections($url);

        file_put_contents($filePath, json_encode($players, JSON_PRETTY_PRINT));

        return $players;
    }
}
