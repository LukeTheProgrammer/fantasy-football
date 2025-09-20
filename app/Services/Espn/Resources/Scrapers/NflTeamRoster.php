<?php

namespace App\Services\Espn\Resources\Scrapers;

use Exception;
use App\Models\Team;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class NflTeamRoster
{
    public const TEAMS = [
        'arizona-cardinals'     => [
            'abb' => 'ARI',
            'url' => 'ari/arizona-cardinals',
        ],
        'atlanta-falcons'       => [
            'abb' => 'ATL',
            'url' => 'atl/atlanta-falcons',
        ],
        'baltimore-ravens'      => [
            'abb' => 'BAL',
            'url' => 'bal/baltimore-ravens',
        ],
        'buffalo-bills'         => [
            'abb' => 'BUF',
            'url' => 'buf/buffalo-bills',
        ],
        'carolina-panthers'     => [
            'abb' => 'CAR',
            'url' => 'car/carolina-panthers',
        ],
        'chicago-bears'         => [
            'abb' => 'CHI',
            'url' => 'chi/chicago-bears',
        ],
        'cincinnati-bengals'    => [
            'abb' => 'CIN',
            'url' => 'cin/cincinnati-bengals',
        ],
        'cleveland-browns'      => [
            'abb' => 'CLE',
            'url' => 'cle/cleveland-browns',
        ],
        'dallas-cowboys'        => [
            'abb' => 'DAL',
            'url' => 'dal/dallas-cowboys',
        ],
        'denver-broncos'        => [
            'abb' => 'DEN',
            'url' => 'den/denver-broncos',
        ],
        'detroit-lions'         => [
            'abb' => 'DET',
            'url' => 'det/detroit-lions',
        ],
        'green-bay-packers'     => [
            'abb' => 'GB',
            'url' => 'gb/green-bay-packers',
        ],
        'houston-texans'        => [
            'abb' => 'HOU',
            'url' => 'hou/houston-texans',
        ],
        'indianapolis-colts'    => [
            'abb' => 'IND',
            'url' => 'ind/indianapolis-colts',
        ],
        'jacksonville-jaguars'  => [
            'abb' => 'JAX',
            'url' => 'jax/jacksonville-jaguars',
        ],
        'kansas-city-chiefs'    => [
            'abb' => 'KC',
            'url' => 'kc/kansas-city-chiefs',
        ],
        'las-vegas-raiders'     => [
            'abb' => 'LV',
            'url' => 'lv/las-vegas-raiders',
        ],
        'los-angeles-chargers'  => [
            'abb' => 'LAC',
            'url' => 'lac/los-angeles-chargers',
        ],
        'los-angeles-rams'      => [
            'abb' => 'LAR',
            'url' => 'lar/los-angeles-rams',
        ],
        'miami-dolphins'        => [
            'abb' => 'MIA',
            'url' => 'mia/miami-dolphins',
        ],
        'minnesota-vikings'     => [
            'abb' => 'MIN',
            'url' => 'min/minnesota-vikings',
        ],
        'new-england-patriots'  => [
            'abb' => 'NE',
            'url' => 'ne/new-england-patriots',
        ],
        'new-orleans-saints'    => [
            'abb' => 'NO',
            'url' => 'no/new-orleans-saints',
        ],
        'new-york-giants'       => [
            'abb' => 'NYG',
            'url' => 'nyg/new-york-giants',
        ],
        'new-york-jets'         => [
            'abb' => 'NYJ',
            'url' => 'nyj/new-york-jets',
        ],
        'philadelphia-eagles'   => [
            'abb' => 'PHI',
            'url' => 'phi/philadelphia-eagles',
        ],
        'pittsburgh-steelers'   => [
            'abb' => 'PIT',
            'url' => 'pit/pittsburgh-steelers',
        ],
        'san-francisco-49ers'   => [
            'abb' => 'SF',
            'url' => 'sf/san-francisco-49ers',
        ],
        'seattle-seahawks'      => [
            'abb' => 'SEA',
            'url' => 'sea/seattle-seahawks',
        ],
        'tampa-bay-buccaneers'  => [
            'abb' => 'TB',
            'url' => 'tb/tampa-bay-buccaneers',
        ],
        'tennessee-titans'      => [
            'abb' => 'TEN',
            'url' => 'ten/tennessee-titans',
        ],
        'washington-commanders' => [
            'abb' => 'WSH',
            'url' => 'wsh/washington-commanders',
        ],
    ];

    public function get(string $teamName)
    {
        $team = Arr::get(static::TEAMS, $teamName);
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
    protected function extractGroupsJson(string $html): ?string
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
    protected function collectPlayersFromGroups(array $groups): array
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
