<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueShowResource;
use App\Models\League;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class LeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // One card per league, not per season: the most recent season represents
        // the league and carries the list of seasons available for it.
        $leagues = League::with(['members', 'draft'])
            ->orderByDesc('season')
            ->get()
            ->groupBy(fn (League $league) => $league->platform . ':' . $league->platform_id)
            ->map(function ($seasons) {
                $league = $seasons->first();

                $league->setAttribute('seasons', $this->seasonOptions($seasons));

                return $league;
            })
            ->values();

        return Inertia::render('leagues/LeaguesIndexPage', [
            'leagues' => $leagues,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        return Inertia::render('leagues/CreateLeaguePage');
    }

    /**
     * Display the specified resource.
     */
    public function show(League $league, Request $request)
    {
        $league->load([
            'creator',
            'draft.picks' => ['leagueMember', 'player'],
            'matchups'    => ['homeTeam', 'awayTeam'],
            'members'     => [
                'rosters' => [
                    'nflGame',
                    'player',
                ],
                'user',
            ],
            'settings',
        ]);

        return Inertia::render('leagues/ShowLeaguePage', [
            'league'  => new LeagueShowResource($league),
            'seasons' => $this->seasonOptions(
                League::sameLeagueAs($league)->orderByDesc('season')->get()
            ),
        ]);
    }

    /**
     * The seasons a league has been played, newest first, each pointing at the
     * league row for that season.
     */
    private function seasonOptions(Collection $leagues): Collection
    {
        return $leagues
            ->sortByDesc('season')
            ->map(fn (League $league) => [
                'id'     => $league->id,
                'season' => $league->season,
            ])
            ->values();
    }

    /**
     * Display the specified resource to edit.
     */
    public function edit(League $league)
    {
        $league->load(['creator', 'settings', 'members.user']);

        return Inertia::render('leagues/EditLeaguePage', [
            'league' => $league,
        ]);
    }
}
