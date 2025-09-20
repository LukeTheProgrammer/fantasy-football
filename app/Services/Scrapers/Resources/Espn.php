<?php

namespace App\Services\Scrapers\Resources;

use App\Models\Team;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Espn extends BaseScraperResource
{
    public const TEAMS = [
        'ARI' => 'ari/arizona-cardinals',
        'ATL' => 'atl/atlanta-falcons',
        'BAL' => 'bal/baltimore-ravens',
        'BUF' => 'buf/buffalo-bills',
        'CAR' => 'car/carolina-panthers',
        'CHI' => 'chi/chicago-bears',
        'CIN' => 'cin/cincinnati-bengals',
        'CLE' => 'cle/cleveland-browns',
        'DAL' => 'dal/dallas-cowboys',
        'DEN' => 'den/denver-broncos',
        'DET' => 'det/detroit-lions',
        'GB'  => 'gnb/green-bay-packers',
        'HOU' => 'htx/houston-texans',
        'IND' => 'clt/indianapolis-colts',
        'JAX' => 'jax/jacksonville-jaguars',
        'KC'  => 'kan/kansas-city-chiefs',
        'LV'  => 'rai/las-vegas-raiders',
        'LAR' => 'ram/los-angeles-rams',
        'MIA' => 'mia/miami-dolphins',
        'MIN' => 'min/minnesota-vikings',
        'NE'  => 'nwe/new-england-patriots',
        'NO'  => 'nor/new-orleans-saints',
        'NYG' => 'nyg/new-york-giants',
        'NYJ' => 'nyj/new-york-jets',
        'PHI' => 'phi/philadelphia-eagles',
        'PIT' => 'pit/pittsburgh-steelers',
        'SEA' => 'sea/seattle-seahawks',
        'SF'  => 'sfo/san-francisco-49ers',
        'TB'  => 'tam/tampa-bay-buccaneers',
        'TEN' => 'oti/tennessee-titans',
        'LAC' => 'sdg/los-angeles-chargers',
        'WSH' => 'was/washington-commanders',
    ];

    public function getTeamRoster(string $teamAbb)
    {
        $team = Arr::get(static::TEAMS, $teamAbb);
        $url = 'https://www.espn.com/nfl/team/roster/_/name/' . Arr::get($team, 'url');

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ])->get($url);

        if (! $response->ok()) {
            throw new Exception('Failed to fetch URL: ' . $url . ' (status ' . $response->status() . ')');
        }

        $html = $response->body();
        if ($html === '' || $html === null) {
            throw new Exception('Received empty response from URL: ' . $url);
        }

        $groupsJson = $this->extractGroupsJson($html);
        if ($groupsJson === null) {
            throw new Exception('Could not locate roster groups JSON within the HTML.');
        }

        $groups = json_decode($groupsJson, true);
        if (! is_array($groups)) {
            throw new Exception('Failed to decode roster groups JSON.');
        }

        $teamModel = Team::forAbbreviation(Arr::get($team, 'abb'))->first();

        return [
            'team'   => $teamModel,
            'roster' => $this->collectPlayersFromGroups($groups)
        ];
    }

    /**
     * Extract the JSON array assigned to "groups" from the ESPN HTML.
     * We locate the first '[' after the "groups" key and then
     * scan forward tracking bracket depth until the matching ']'.
     */
    private function extractGroupsJson(string $html): ?string
    {
        $keyPos = strpos($html, '"groups"');
        if ($keyPos === false) {
            return null;
        }

        // Find the opening bracket '[' after the key
        $openBracketPos = strpos($html, '[', $keyPos);
        if ($openBracketPos === false) {
            return null;
        }

        $len = strlen($html);
        $depth = 0;
        $inString = false;
        $escape = false;
        for ($i = $openBracketPos; $i < $len; $i++) {
            $ch = $html[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }
                continue;
            } else {
                if ($ch === '"') {
                    $inString = true;
                    continue;
                }
                if ($ch === '[') {
                    $depth++;
                } elseif ($ch === ']') {
                    $depth--;
                    if ($depth === 0) {
                        // Extract substring including brackets
                        $json = substr($html, $openBracketPos, $i - $openBracketPos + 1);
                        return $json;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Flatten players from groups into a simple list of associative arrays.
     *
     * @param array $groups
     * @return array<int, array<string, mixed>>
     */
    private function collectPlayersFromGroups(array $groups): array
    {
        $players = [];
        foreach ($groups as $group) {
            if (!isset($group['athletes']) || !is_array($group['athletes'])) {
                continue;
            }
            foreach ($group['athletes'] as $athlete) {
                if (!is_array($athlete)) {
                    continue;
                }
                $players[] = [
                    'id'        => $athlete['id']        ?? null,
                    'name'      => $athlete['name']      ?? ($athlete['shortName'] ?? null),
                    'position'  => $athlete['position']  ?? null,
                    'jersey'    => $athlete['jersey']    ?? null,
                    'age'       => $athlete['age']       ?? null,
                    'height'    => $athlete['height']    ?? null,
                    'weight'    => $athlete['weight']    ?? null,
                    'birthDate' => $athlete['birthDate'] ?? null,
                    'headshot'  => $athlete['headshot']  ?? null,
                    'group'     => $group['name']        ?? null,
                ];
            }
        }

        return $players;
    }
}
