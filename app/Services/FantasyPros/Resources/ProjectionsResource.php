<?php

namespace App\Services\FantasyPros\Resources;

use App\Models\Season;
use App\Models\Week;
use App\Services\FantasyPros\Formatters\ProjectionFormatter;
use App\Traits\LoadsJsonFiles;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ProjectionsResource extends BaseResource
{
    use LoadsJsonFiles;

    public array $sources = [
        '2-qb'    => 'https://www.fantasypros.com/nfl/rankings/superflex.php',
        'qb'      => 'https://www.fantasypros.com/nfl/rankings/qb.php',
        'std-rb'  => 'https://www.fantasypros.com/nfl/rankings/rb.php',
        'std-wr'  => 'https://www.fantasypros.com/nfl/rankings/wr.php',
        'std-te'  => 'https://www.fantasypros.com/nfl/rankings/te.php',
        'std-k'   => 'https://www.fantasypros.com/nfl/rankings/k.php',
        'std-dst' => 'https://www.fantasypros.com/nfl/rankings/dst.php',
        'half-rb' => 'https://www.fantasypros.com/nfl/rankings/half-point-ppr-rb.php',
        'half-wr' => 'https://www.fantasypros.com/nfl/rankings/half-point-ppr-wr.php',
        'half-te' => 'https://www.fantasypros.com/nfl/rankings/half-point-ppr-te.php',
        'ppr-rb'  => 'https://www.fantasypros.com/nfl/rankings/ppr-rb.php',
        'ppr-wr'  => 'https://www.fantasypros.com/nfl/rankings/ppr-wr.php',
        'ppr-te'  => 'https://www.fantasypros.com/nfl/rankings/ppr-te.php',
    ];

    public ?int $season = null;

    public ?int $week = null;

    private ?int $currentSeason = null;

    private ?int $currentWeek = null;

    private bool $isCurrentWeek = false;

    private bool $pullNewData = false;

    private string $dataDir = '';

    private string $archiveDir = '';

    public function __construct()
    {
        $this->currentSeason = Season::current()->first()->id;
        $this->currentWeek = Week::current()->first()->week;
    }

    public function getProjections(?int $season = null, ?int $week = null)
    {
        $proj = [];

        foreach ($this->sources as $source => $url) {
            $proj[$source] = $this->getProjection($source, $season, $week);
        }

        return $proj;
    }

    public function getProjection(string $source, ?int $season = null, ?int $week = null)
    {
        if (! isset($this->sources[$source])) {
            throw new InvalidArgumentException("Invalid source: $source");
        }

        $this->season = $season ?? $this->currentSeason;
        $this->week = $week ?? $this->currentWeek;

        $this->setUp();

        if ($this->shouldPull($source)) {
            $this->pullProjection($source);
        }

        return $this->getPlayers($source);
    }


    /**
     * Just for formatting older files.
     */
    public function processDir(string $path)
    {
        $files = array_filter(scandir($path), fn ($file) => ! str_starts_with($file, '.'));

        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            // dump($filePath);

            if (is_dir($filePath)) {
                $this->processDir($filePath);
                continue;
            }

            if (! str_ends_with($filePath, '.html')) {
                // dump('Not HTML: ' . $file);
                continue;
            }

            // /var/www/html/fantasy-football/storage/data/fantasy-pros/projections/2025/week-1/2025-09-03/half-rb.html
            $pathData = array_values(array_filter(explode('/',
                str_replace(storage_path('data/fantasy-pros/projections'), '', $filePath)
            )));

            $this->season = (int) $pathData[0];
            $this->week = (int) str_replace('week-', '', $pathData[1]);

            $html = file_get_contents($filePath);
            $players = $this->parseHtml($html);

            $jsonFP = $path . '/' . str_replace('.html', '.json', $file);
            dump('Saving ' . $jsonFP);
            file_put_contents($jsonFP, json_encode($players, JSON_PRETTY_PRINT));

            $projections = $this->formatPlayers($players);

            $formattedJsonFP = str_replace('.json', '-formatted.json', $jsonFP);
            dump('Saving ' . $formattedJsonFP);
            file_put_contents($formattedJsonFP, json_encode($projections, JSON_PRETTY_PRINT));
        }
    }

    //

    private function setUp()
    {
        $this->isCurrentWeek = (
            $this->season === $this->currentSeason &&
            $this->week === $this->currentWeek
        );

        $dir = storage_path(implode('/', [
            'data',
            'fantasy-pros',
            'projections',
            $this->season,
            'week-' . $this->week,
        ]));

        $this->dataDir = $dir;

        $this->archiveDir = $dir . '/' . date('Y-m-d');

        if (! is_dir($this->dataDir)) {
            Log::debug('Creating Dir', [__CLASS__, 1, $this->dataDir]);
            dump('Creating Dir' . $this->dataDir);
            mkdir($this->dataDir, 0775, true);
        }

        if ($this->isCurrentWeek && ! is_dir($this->archiveDir)) {
            Log::debug('Creating Dir', [__CLASS__, 2, $this->archiveDir]);
            dump('Creating Dir' . $this->archiveDir);
            mkdir($this->archiveDir, 0775, true);
        }
    }

    private function shouldPull(string $source): bool
    {
        return ! $this->dataExists($source) || (! $this->archiveExists($source) && $this->isCurrentWeek);
    }

    private function dataExists(string $source): bool
    {
        $fileName = $source . '.json';
        $dataPath = $this->dataDir . '/' . $fileName;

        return file_exists($dataPath);
    }

    private function archiveExists(string $source): bool
    {
        $fileName = $source . '.json';
        $dataPath = $this->archiveDir . '/' . $fileName;

        return file_exists($dataPath);
    }

    private function pullProjection(string $source, ?int $season = null, ?int $week = null): bool|array
    {
        $this->season = $season ?? $this->currentSeason;
        $this->week = $week ?? $this->currentWeek;

        $this->setUp();

        $html = $this->pullHtml($source);

        if (! $html) {
            return false;
        }

        $players = $this->parseHtml($html);

        $this->savePlayers($source, $players);

        return $players;
    }

    private function getPlayers(string $source): bool|array
    {
        $fileName = $source . '.json';
        $dataPath = $this->dataDir . '/' . $fileName;

        $data = $this->loadJsonFile($dataPath);

        return empty($data) ? false : $data;
    }

    private function formatPlayers(array $players)
    {
        return ProjectionFormatter::from($players, $this->season, $this->week);
    }

    private function savePlayers(string $source, array $players): bool
    {
        $fileName = $source . '.json';
        $dataPath = $this->dataDir . '/' . $fileName;
        $archivePath = $this->archiveDir . '/' . $fileName;

        file_put_contents($dataPath, json_encode($players, JSON_PRETTY_PRINT));
        file_put_contents($archivePath, json_encode($players, JSON_PRETTY_PRINT));

        $formattedFileName = $source . '-formatted.json';
        $formattedDataPath = $this->dataDir . '/' . $formattedFileName;
        $formattedArchivePath = $this->archiveDir . '/' . $formattedFileName;

        $projections = $this->formatPlayers($players);

        file_put_contents($formattedDataPath, json_encode($projections, JSON_PRETTY_PRINT));
        file_put_contents($formattedArchivePath, json_encode($projections, JSON_PRETTY_PRINT));

        return true;
    }

    private function pullHtml(string $source): bool|string
    {
        $fileName = $source . '.html';
        $dataPath = $this->dataDir . '/' . $fileName;
        $archivePath = $this->archiveDir . '/' . $fileName;

        $url = $this->sources[$source];

        $response = Http::get($url);

        if (! $response->successful()) {
            dd([
                'failure',
                $response->status(),
                $response->body(),
            ]);
        }

        $html = $response->body();

        file_put_contents($dataPath, $html);
        file_put_contents($archivePath, $html);

        return $html;
    }

    /**
     * Extract players array from the embedded ecrData JSON inside the HTML.
     * Falls back to extracting only the players array if full object parsing fails.
     */
    private function parseHtml(string $html): array
    {
        // Primary: capture the full ecrData object
        if (preg_match('/var\s+ecrData\s*=\s*(\{.*?\});/s', $html, $m)) {
            $json = $m[1] ?? '';
            // Some pages may end with a semicolon; ensure we strip it
            $json = rtrim($json);
            $json = preg_replace('/;\s*$/', '', $json);

            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $players = Arr::get($data, 'players');
                if (is_array($players)) {
                    return $this->filterPlayers($players);
                }
            }
        }

        // Fallback: capture only the players array contents and decode
        if (preg_match('/"players"\s*:\s*\[(.*?)\]/s', $html, $m2)) {
            $playersJson = '[' . ($m2[1] ?? '') . ']';
            $players = json_decode($playersJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($players)) {
                return $this->filterPlayers($players);
            }
        }

        return [];
    }

    private function filterPlayers(array $players): array
    {
        return array_filter(
            $players,
            function ($p) {
                return (
                    ! empty(Arr::get($p, 'player_id')) &&
                    ! empty(Arr::get($p, 'player_name')) &&
                    ! empty(Arr::get($p, 'player_position_id')) &&
                    ! empty(Arr::get($p, 'player_team_id'))
                );
            }
        );
    }
}
