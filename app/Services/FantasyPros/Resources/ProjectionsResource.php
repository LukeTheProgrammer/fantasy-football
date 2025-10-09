<?php

namespace App\Services\FantasyPros\Resources;

use App\Models\Season;
use App\Models\Week;
use App\Services\FantasyPros\Formatters\ProjectionFormatter;
use App\Traits\LoadsJsonFiles;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
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

    public ?int $currentSeason = null;

    public ?int $currentWeek = null;

    public bool $isCurrentWeek = false;

    public function __construct()
    {
        $this->currentSeason = Season::current()->first()->id;
        $this->currentWeek = Week::current()->first()->week;
    }

    public function getAllProjections(?int $season = null, ?int $week = null)
    {
        $proj = [];

        foreach ($this->sources as $source => $url) {
            $proj[$source] = $this->getProjections($source, $season, $week);
        }

        return $proj;
    }

    public function getProjections(string $source, ?int $season = null, ?int $week = null)
    {
        if (! isset($this->sources[$source])) {
            throw new InvalidArgumentException("Invalid source: $source");
        }

        $this->season = $season ?? $this->currentSeason;
        $this->week = $week ?? $this->currentWeek;

        $this->setIsCurrentWeek();

        if ($players = $this->getPlayers($source)) {
            return $players;
        }

        return $this->pullProjections($source, $season, $week);
    }

    public function pullProjections(string $source, ?int $season = null, ?int $week = null)
    {
        $this->season = $season ?? $this->currentSeason;
        $this->week = $week ?? $this->currentWeek;

        $this->setIsCurrentWeek();

        $html = $this->getHtml($source);

        if (! $html) {
            return false;
        }

        $players = $this->parseHtml($html);

        $this->savePlayers($source, $players);

        return $players;
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

    private function setIsCurrentWeek(): void
    {
        $this->isCurrentWeek = (
            $this->season === $this->currentSeason &&
            $this->week === $this->currentWeek
        );
    }

    private function getFileDir(): string
    {
        $dir = storage_path(implode('/', [
            'data',
            'fantasy-pros',
            'projections',
            $this->season,
            'week-' . $this->week,
        ]));

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (! is_dir($dir . '/' . date('Y-m-d'))) {
            mkdir($dir . '/' . date('Y-m-d'), 0775, true);
        }

        return $dir;
    }

    private function getHtml(string $source): bool|string
    {
        $fileName = $source . '.html';
        $filePath = $this->getFileDir() . '/' . $fileName;

        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }

        // FP does not have archival data, so if we didn't pull it
        // at the time, we can't pull it now.
        if (! $this->isCurrentWeek) {
            return false;
        }

        $archiveFilePath = $this->getFileDir() . '/' . date('Y-m-d') . '/' . $fileName;

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

        file_put_contents($filePath, $html);
        file_put_contents($archiveFilePath, $html);

        return $html;
    }

    private function getPlayers(string $source): bool|array
    {
        $fileName = $source . '.json';
        $filePath = $this->getFileDir() . '/' . $fileName;

        $data = $this->loadJsonFile($filePath);

        if (! empty($data)) {
            // dump('Loaded: '. $filePath);
        }

        return empty($data) ? false : $data;
    }

    private function formatPlayers(array $players)
    {
        return ProjectionFormatter::from($players, $this->season, $this->week);
    }

    private function savePlayers(string $source, array $players): bool
    {
        $fileName = $source . '.json';
        $filePath = $this->getFileDir() . '/' . $fileName;
        $archiveFilePath = $this->getFileDir() . '/' . date('Y-m-d') . '/' . $fileName;

        file_put_contents($filePath, json_encode($players, JSON_PRETTY_PRINT));
        file_put_contents($archiveFilePath, json_encode($players, JSON_PRETTY_PRINT));

        $formattedFileName = $source . '-formatted.json';
        $formattedFilePath = $this->getFileDir() . '/' . $formattedFileName;
        $formattedArchiveFilePath = $this->getFileDir() . '/' . date('Y-m-d') . '/' . $formattedFileName;

        $projections = $this->formatPlayers($players);

        file_put_contents($formattedFilePath, json_encode($projections, JSON_PRETTY_PRINT));
        file_put_contents($formattedArchiveFilePath, json_encode($projections, JSON_PRETTY_PRINT));

        return true;
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
