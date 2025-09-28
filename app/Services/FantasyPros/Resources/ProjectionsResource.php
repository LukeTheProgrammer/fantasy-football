<?php

namespace App\Services\FantasyPros\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ProjectionsResource extends BaseResource
{
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

    public function getProjections(string $source)
    {
        if (! isset($this->sources[$source])) {
            throw new InvalidArgumentException("Invalid source: $source");
        }

        $filePath = $this->getSourceFilePath($source);

        if (file_exists($filePath)) {
            $html = file_get_contents($filePath);
            return $this->parseHtml($html);
        }

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

        $players = $this->parseHtml($html);

        return $players ?? [];
    }

    private function getSourceFilePath(string $source): string
    {
        $dir = storage_path('data/fantasy-pros/projections/' . date('Y-m-d'));

        if (! file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir . '/' . $source . '.html';
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
                    return $players;
                }
            }
        }

        // Fallback: capture only the players array contents and decode
        if (preg_match('/"players"\s*:\s*\[(.*?)\]/s', $html, $m2)) {
            $playersJson = '[' . ($m2[1] ?? '') . ']';
            $players = json_decode($playersJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($players)) {
                return $players;
            }
        }

        return [];
    }
}
