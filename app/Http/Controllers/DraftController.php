<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\DraftRanking;
use App\Models\League;
use App\Models\Player;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    /**
     * Display a listing of drafts for a league.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('drafts/index', [
            'drafts' => $user->drafts()
                ->with(['league.members'])
                ->orderBy('draft_date', 'desc')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new draft.
     */
    public function create(League $league)
    {
        return Inertia::render('drafts/create', [
            'league' => $league,
        ]);
    }

    /**
     * Display the specified draft.
     */
    public function show(Draft $draft)
    {
        $draft->load([
            'league.members',
            'picks' => [
                'leagueMember.user',
                'player' => [
                    'position',
                    'team',
                ],
            ],
        ]);

        return Inertia::render('drafts/show', [
            'draft' => $draft,
        ]);
    }

    /**
     * Show the form for editing the specified draft.
     */
    public function edit(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        // Don't allow editing completed drafts
        if ($draft->is_completed) {
            return redirect()->route('drafts.show', $draft)
                ->with('error', 'Cannot edit a completed draft');
        }

        return Inertia::render('drafts/edit', [
            'league' => $league,
            'draft' => $draft,
        ]);
    }

    /**
     * Display the draft board for an active draft.
     */
    public function draftRoom(Draft $draft)
    {
        $draft->load([
            'league.members.user',
            'picks' => [
                'leagueMember.user',
                'player' => [
                    'position',
                    'team',
                ],
            ],
        ]);

        // Get available players for drafting
        $availablePlayers = DraftRanking::where('season', $draft->league->season)
            ->where(function ($q) {
                $q->orWhere('average_rank', '>', 0)
                    ->orWhere('average_value', '>', 0)
                    ->orWhere('fp_standard_ranking', '>', 0)
                    ->orWhere('fp_standard_adp', '>', 0)
                    ->orWhere('fp_ppr_ranking', '>', 0)
                    ->orWhere('fp_ppr_adp', '>', 0);
            })
            ->with(['player.position', 'player.team'])
            ->get();

        return Inertia::render('drafts/draft-room', [
            'draft' => $draft,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    /**
     * Display the results of a completed draft.
     */
    public function results(League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            abort(404, 'Draft does not belong to this league');
        }

        $draft->load([
            'picks.leagueMember.user',
            'picks.player.position',
            'picks.player.team',
        ]);

        // Group picks by league member to show each team's draft results
        $teamResults = $draft->picks()
            ->whereNotNull('player_id')
            ->with(['leagueMember.user', 'player.position', 'player.team'])
            ->get()
            ->groupBy('league_member_id');

        return Inertia::render('drafts/results', [
            'league' => $league,
            'draft' => $draft,
            'teamResults' => $teamResults,
        ]);
    }
}
