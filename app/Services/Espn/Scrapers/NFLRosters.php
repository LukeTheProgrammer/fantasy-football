<?php

namespace App\Services\Espn\Scrapers;

use App\Enums\NFLTeams;
use App\Models\Team;
use App\Traits\LoadsJsonFiles;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class NFLRosters
{
    use LoadsJsonFiles;

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
        'GB'  => 'gb/green-bay-packers',
        'HOU' => 'hou/houston-texans',
        'IND' => 'ind/indianapolis-colts',
        'JAX' => 'jax/jacksonville-jaguars',
        'KC'  => 'kc/kansas-city-chiefs',
        'LAC' => 'lac/los-angeles-chargers',
        'LAR' => 'lar/los-angeles-rams',
        'LV'  => 'lv/las-vegas-raiders',
        'MIA' => 'mia/miami-dolphins',
        'MIN' => 'min/minnesota-vikings',
        'NE'  => 'ne/new-england-patriots',
        'NO'  => 'no/new-orleans-saints',
        'NYG' => 'nyg/new-york-giants',
        'NYJ' => 'nyj/new-york-jets',
        'PHI' => 'phi/philadelphia-eagles',
        'PIT' => 'pit/pittsburgh-steelers',
        'SEA' => 'sea/seattle-seahawks',
        'SF'  => 'sf/san-francisco-49ers',
        'TB'  => 'tb/tampa-bay-buccaneers',
        'TEN' => 'ten/tennessee-titans',
        'WSH' => 'wsh/washington-commanders',
    ];

    public function __construct(protected ?string $cacheFilePath = null)
    {
        //
    }

    /**
     * The espn.com roster page now answers scrapes with a 202 bot challenge,
     * so this reads the site API instead. The returned shape is unchanged.
     */
    public function getTeamRoster(string|NFLTeams|Team $team, int|string|null $season = null)
    {
        if (! $team instanceof Team) {
            $team  = Team::find(($team instanceof NFLTeams) ? $team->value : $team);
        }

        if (! $team instanceof Team) {
            throw new Exception('Team not found: ' . json_encode($team));
        }

        $season = $season ?? (int) date('Y');

        $cacheFileParams = [$season, $team->id];

        if ($cache = $this->getCache($cacheFileParams)) {
            return $cache;
        }

        $url = 'https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/'
            . $team->espn_id . '/roster';

        $response = Http::get($url, ['season' => $season]);

        if (! $response->ok()) {
            throw new Exception('Failed to fetch URL: ' . $url . ' (status ' . $response->status() . ')');
        }

        $groups = Arr::get($response->json(), 'athletes');

        if (! is_array($groups)) {
            throw new Exception('No roster groups in response for ' . $team->id . ' (' . $season . ').');
        }

        $players = $this->collectPlayersFromGroups($groups);

        $this->setCache($cacheFileParams, $players);

        return $players;
    }

    protected function getCachePath(array $params = [])
    {
        return storage_path('data/espn/nfl-teams/rosters/' . implode('-', $params) . '.json');
    }

    protected function getCache(array $params = [])
    {
        $path = $this->cacheFilePath ?? $this->getCachePath($params);

        return $this->loadJsonFile($path);
    }

    protected function setCache(array $params = [], array $data = [])
    {
        $path = $this->cacheFilePath ?? $this->getCachePath($params);

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
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
    /**
     * The API returns an ISO timestamp where the roster page gave m/d/y.
     */
    protected function formatBirthDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('m/d/y');
        } catch (Exception) {
            return null;
        }
    }

    protected function collectPlayersFromGroups(array $groups): array
    {
        $players = [];
        foreach ($groups as $group) {
            // The API nests players under 'items'; the old roster page used 'athletes'.
            $athletes = $group['items'] ?? $group['athletes'] ?? null;

            if (! is_array($athletes)) {
                continue;
            }
            foreach ($athletes as $athlete) {
                if (!is_array($athlete)) {
                    continue;
                }
                $players[] = [
                    'id'        => $athlete['id'] ?? null,
                    'name'      => $athlete['fullName'] ?? ($athlete['displayName'] ?? null),
                    'position'  => Arr::get($athlete, 'position.abbreviation'),
                    'jersey'    => $athlete['jersey'] ?? null,
                    'age'       => $athlete['age'] ?? null,
                    'height'    => $athlete['displayHeight'] ?? null,
                    'weight'    => $athlete['displayWeight'] ?? null,
                    'birthDate' => $this->formatBirthDate($athlete['dateOfBirth'] ?? null),
                    'headshot'  => Arr::get($athlete, 'headshot.href'),
                    'group'     => $group['position'] ?? ($group['name'] ?? null),
                ];
            }
        }

        return $players;
    }
}
