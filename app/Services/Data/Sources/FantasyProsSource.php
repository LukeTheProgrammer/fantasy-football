<?php

namespace App\Services\Data\Sources;

use App\Enums\NFLTeams;
use App\Models\Position;
use App\Models\Team;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FantasyProsSource extends BaseSource
{
    public const TEAMS = [
        'ARI' => 'crd',
        'ATL' => 'atl',
        'BAL' => 'rav',
        'BUF' => 'buf',
        'CAR' => 'car',
        'CHI' => 'chi',
        'CIN' => 'cin',
        'CLE' => 'cle',
        'DAL' => 'dal',
        'DEN' => 'den',
        'DET' => 'det',
        'GB'  => 'gnb',
        'HOU' => 'htx',
        'IND' => 'clt',
        'JAX' => 'jax',
        'KC'  => 'kan',
        'LV'  => 'rai',
        'LAR' => 'ram',
        'MIA' => 'mia',
        'MIN' => 'min',
        'NE'  => 'nwe',
        'NO'  => 'nor',
        'NYG' => 'nyg',
        'NYJ' => 'nyj',
        'PHI' => 'phi',
        'PIT' => 'pit',
        'SEA' => 'sea',
        'SF'  => 'sfo',
        'TB'  => 'tam',
        'TEN' => 'oti',
        'LAC' => 'sdg',
        'WSH' => 'was',
    ];

    private ?Team $team = null;

    private ?Collection $positions = null;

    public function getTeamRoster(NFLTeams $teamAbb, int $year)
    {
        $this->team = Team::forAbbreviation($teamAbb)->first();

        $this->positions = Position::all()->keyBy('abbreviation');

        $teamKey = Arr::get(self::TEAMS, $this->team->abbreviation);

        if (! $teamKey) {
            throw new Exception('Invalid team abbreviation: ' . $teamAbb);
        }

        $url = "https://www.pro-football-reference.com/teams/{$teamKey}/{$year}_roster.htm"; //#roster

        $response = Http::get($url);

        if ($response->status() === 429) {
            throw new Exception('Too many requests');
        }

        $html = $response->body();

        $data = $this->parseRosterFromHtml($html);

        return $this->formatData($data);
    }

    private function cleanNames(array $data)
    {
        $data['first_name'] = preg_replace('/\s\(.{1,}\)/', '', $data['first_name']);
        $data['last_name'] = preg_replace('/\s\(.{1,}\)/', '', $data['last_name']);
        $data['full_name'] = preg_replace('/\s\(.{1,}\)/', '', $data['full_name']);

        return $data;
    }

    /**
     * Given full PFR page HTML, locate the commented roster table and extract players.
     */
    private function parseRosterFromHtml(string $html): array
    {
        $tableHtml = $this->extractRosterTableHtml($html);
        if ($tableHtml === null) {
            return [];
        }

        // Load table HTML fragment into DOM
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $tableHtml);
        libxml_clear_errors();
        if (! $loaded) {
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query("//table[@id='roster']//tbody/tr");
        if (! $rows || $rows->length === 0) {
            return [];
        }

        $players = [];
        foreach ($rows as $tr) {
            if (! $tr instanceof \DOMElement) {
                continue;
            }

            $getText = function (?\DOMElement $el): ?string {
                if (! $el) return null;
                return trim(preg_replace('/\s+/', ' ', $el->textContent ?? '')) ?: null;
            };

            $queryOne = function (string $q) use ($xpath, $tr): ?\DOMElement {
                $n = $xpath->query($q, $tr);
                return ($n && $n->length > 0) ? $n->item(0) : null;
            };

            $numberEl  = $queryOne(".//th[@data-stat='uniform_number']");
            $playerEl  = $queryOne(".//td[@data-stat='player']");
            $ageEl     = $queryOne(".//td[@data-stat='age']");
            $posEl     = $queryOne(".//td[@data-stat='pos']");
            $gEl       = $queryOne(".//td[@data-stat='g']");
            $gsEl      = $queryOne(".//td[@data-stat='gs']");
            $wtEl      = $queryOne(".//td[@data-stat='weight']");
            $htEl      = $queryOne(".//td[@data-stat='height']");
            $collegeEl = $queryOne(".//td[@data-stat='college_id']");
            $bdEl      = $queryOne(".//td[@data-stat='birth_date_mod']");
            $expEl     = $queryOne(".//td[@data-stat='experience']");
            $avEl      = $queryOne(".//td[@data-stat='av']");
            $draftEl   = $queryOne(".//td[@data-stat='draft_info']");

            // Pull additional identifiers from attributes
            $pfrId = null; // Often in data-append-csv on the player cell
            $playerUrl = null;
            if ($playerEl) {
                $pfrId = $playerEl->getAttribute('data-append-csv') ?: null;
                $a = $xpath->query('.//a', $playerEl);
                if ($a && $a->length > 0) {
                    /** @var \DOMElement $a0 */
                    $a0 = $a->item(0);
                    $href = $a0?->getAttribute('href');
                    if ($href) {
                        // Normalize to absolute PFR URL
                        $playerUrl = str_starts_with($href, 'http')
                            ? $href
                            : 'https://www.pro-football-reference.com' . $href;
                    }
                }
            }

            $players[] = [
                'number'      => $getText($numberEl),
                'name'        => $getText($playerEl),
                'age'         => ($t = $getText($ageEl)) !== null ? (int)$t : null,
                'position'    => $getText($posEl),
                'games'       => ($t = $getText($gEl)) !== null && $t !== '' ? (int)$t : null,
                'games_started'=> ($t = $getText($gsEl)) !== null && $t !== '' ? (int)$t : null,
                'weight'      => ($t = $getText($wtEl)) !== null ? (int)$t : null,
                'height'      => $getText($htEl), // e.g., 6-1
                'college'     => $getText($collegeEl),
                'birth_date'  => $getText($bdEl),
                'experience'  => $getText($expEl),
                'av'          => ($t = $getText($avEl)) !== null && $t !== '' ? (int)$t : null,
                'draft_info'  => $getText($draftEl),
                'pfr_id'      => $pfrId,
                'player_url'  => $playerUrl,
            ];
        }

        return $players;
    }

    /**
     * PFR hides data tables inside HTML comments. Find the comment block that
     * contains the roster table (id="roster") and return its inner HTML.
     */
    private function extractRosterTableHtml(string $html): ?string
    {
        // Find all HTML comments
        $matches = [];
        if (!preg_match_all('/<!--(.*?)-->/s', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $commentContent) {
            if (stripos($commentContent, 'id="div_roster"') !== false || stripos($commentContent, 'id="roster"') !== false) {
                // Ensure we only return the specific table container to keep parsing light
                $start = stripos($commentContent, '<div class="table_container"');
                if ($start !== false) {
                    $fragment = substr($commentContent, $start);
                } else {
                    $fragment = $commentContent;
                }
                return $fragment;
            }
        }

        return null;
    }

    private function formatData(array $data): array
    {
        return array_filter(array_map(function (array $player) {
            $pos = $this->positions->get(Arr::get($player, 'position'));

            if (! $pos instanceof Position) {
                return null;
            }

            $fullName = Arr::get($player, 'name');
            $fullName = preg_replace('/\s\(.{1,}\)$/', '', $fullName);
            $nameIndex = strpos($fullName, ' ');

            $height = Arr::get($player, 'height');

            $draft = Arr::get($player, 'draft_info');
            $draftData = ($draft) ? explode(' / ', $draft) : [];

            return [
                'pfr_id'        => Arr::get($player, 'pfr_id'),
                'position_id'   => $pos->id,
                'team_id'       => $this->team->id,
                'first_name'    => substr($fullName, 0, $nameIndex),
                'last_name'     => substr($fullName, $nameIndex + 1),
                'full_name'     => $fullName,
                'jersey_number' => Arr::get($player, 'number'),
                'height'        => ($height) ? str_replace('-', "' ", $height) . '"' : null,
                'weight'        => Arr::get($player, 'weight'),
                'college'       => Arr::get($player, 'college'),
                'draft_team'    => Arr::get($draftData, 0),
                'draft_round'   => Arr::get($draftData, 1),
                'draft_pick'    => Arr::get($draftData, 2),
                'draft_year'    => preg_replace('/[^0-9]/', '', Arr::get($draftData, 3)),
            ];
        }, $data));
    }
}
