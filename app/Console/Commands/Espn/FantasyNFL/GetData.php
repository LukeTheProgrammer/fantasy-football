<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:data
        { league_id? : ESPN League ID }
        { --s2=      : ESPN S2 token }
        { --swid=    : ESPN SWID token }
        { --raw      : Save raw data }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League data from the ESPN API.';

    protected int|string $leagueId;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->leagueId = $this->argument('league_id') ?? config('services.espn.default_league_id');

        $fantasyNFL = Espn::fantasyNFL([
            'leagueId' => $this->leagueId,
            's2' => $this->option('s2') ?? config('services.espn.default_s2'),
            'swid' => $this->option('swid') ?? config('services.espn.default_swid'),
        ]);

        if ($this->option('raw')) {
            $fantasyNFL->returnRaw = true;
        }

        $data = $fantasyNFL->getData();

        $this->saveData('keys', array_keys($data));

        $league = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->saveData($key, $value);
            } else {
                $league[$key] = $value;
            }
        }

        $this->saveData('league', $league);
    }

    protected function filePath(string $key): string
    {
        $parts = ['data','espn','ffl'];

        if ($this->option('raw')) {
            $parts[] = 'raw';
        }

        $parts[] = $this->leagueId . '-getData-' . $key . '.json';

        return storage_path(implode(DIRECTORY_SEPARATOR, $parts));
    }

    protected function saveData(string $key, array $value)
    {
        $path = $this->filePath($key);

        $bytes = file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League $key saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
