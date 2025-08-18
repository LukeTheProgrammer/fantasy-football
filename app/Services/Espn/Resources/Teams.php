<?php

namespace App\Services\Espn\Resources;

use App\Facades\Espn;
use Illuminate\Support\Facades\Http;

/**
 * ATL   /v2/sports/football/leagues/nfl/seasons/2025/teams/1	/athletes, /depthcharts, /record, /events, /projections
 * BUF   /v2/sports/football/leagues/nfl/seasons/2025/teams/2	/athletes, /depthcharts, /record, /events, /projections
 * CHI   /v2/sports/football/leagues/nfl/seasons/2025/teams/3	/athletes, /depthcharts, /record, /events, /projections
 * CIN   /v2/sports/football/leagues/nfl/seasons/2025/teams/4	/athletes, /depthcharts, /record, /events, /projections
 * CLE   /v2/sports/football/leagues/nfl/seasons/2025/teams/5	/athletes, /depthcharts, /record, /events, /projections
 * DAL   /v2/sports/football/leagues/nfl/seasons/2025/teams/6	/athletes, /depthcharts, /record, /events, /projections
 * DEN   /v2/sports/football/leagues/nfl/seasons/2025/teams/7	/athletes, /depthcharts, /record, /events, /projections
 * DET   /v2/sports/football/leagues/nfl/seasons/2025/teams/8	/athletes, /depthcharts, /record, /events, /projections
 * GB    /v2/sports/football/leagues/nfl/seasons/2025/teams/9	/athletes, /depthcharts, /record, /events, /projections
 * TEN   /v2/sports/football/leagues/nfl/seasons/2025/teams/10	/athletes, /depthcharts, /record, /events, /projections
 * IND   /v2/sports/football/leagues/nfl/seasons/2025/teams/11	/athletes, /depthcharts, /record, /events, /projections
 * KC    /v2/sports/football/leagues/nfl/seasons/2025/teams/12	/athletes, /depthcharts, /record, /events, /projections
 * LV    /v2/sports/football/leagues/nfl/seasons/2025/teams/13	/athletes, /depthcharts, /record, /events, /projections
 * LAR   /v2/sports/football/leagues/nfl/seasons/2025/teams/14	/athletes, /depthcharts, /record, /events, /projections
 * MIA   /v2/sports/football/leagues/nfl/seasons/2025/teams/15	/athletes, /depthcharts, /record, /events, /projections
 * MIN   /v2/sports/football/leagues/nfl/seasons/2025/teams/16	/athletes, /depthcharts, /record, /events, /projections
 * NE    /v2/sports/football/leagues/nfl/seasons/2025/teams/17	/athletes, /depthcharts, /record, /events, /projections
 * NO    /v2/sports/football/leagues/nfl/seasons/2025/teams/18	/athletes, /depthcharts, /record, /events, /projections
 * NYG   /v2/sports/football/leagues/nfl/seasons/2025/teams/19	/athletes, /depthcharts, /record, /events, /projections
 * NYJ   /v2/sports/football/leagues/nfl/seasons/2025/teams/20	/athletes, /depthcharts, /record, /events, /projections
 * PHI   /v2/sports/football/leagues/nfl/seasons/2025/teams/21	/athletes, /depthcharts, /record, /events, /projections
 * ARI   /v2/sports/football/leagues/nfl/seasons/2025/teams/22	/athletes, /depthcharts, /record, /events, /projections
 * PIT   /v2/sports/football/leagues/nfl/seasons/2025/teams/23	/athletes, /depthcharts, /record, /events, /projections
 * LAC   /v2/sports/football/leagues/nfl/seasons/2025/teams/24	/athletes, /depthcharts, /record, /events, /projections
 * SF    /v2/sports/football/leagues/nfl/seasons/2025/teams/25	/athletes, /depthcharts, /record, /events, /projections
 * SEA   /v2/sports/football/leagues/nfl/seasons/2025/teams/26	/athletes, /depthcharts, /record, /events, /projections
 * TB    /v2/sports/football/leagues/nfl/seasons/2025/teams/27	/athletes, /depthcharts, /record, /events, /projections
 * WSH   /v2/sports/football/leagues/nfl/seasons/2025/teams/28	/athletes, /depthcharts, /record, /events, /projections
 * CAR   /v2/sports/football/leagues/nfl/seasons/2025/teams/29	/athletes, /depthcharts, /record, /events, /projections
 * JAX   /v2/sports/football/leagues/nfl/seasons/2025/teams/30	/athletes, /depthcharts, /record, /events, /projections
 * BAL   /v2/sports/football/leagues/nfl/seasons/2025/teams/33	/athletes, /depthcharts, /record, /events, /projections
 * HOU   /v2/sports/football/leagues/nfl/seasons/2025/teams/34	/athletes, /depthcharts, /record, /events, /projections
 */
class Teams
{
    public function getTeam(int|string $id)
    {
        $url = Espn::BASE_URL . '/v2/sports/football/leagues/nfl/seasons/2025/teams/' . $id;

        $response = Http::get($url);

        return $response->json();
    }
}
