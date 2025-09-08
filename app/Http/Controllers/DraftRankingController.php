<?php

namespace App\Http\Controllers;

use App\Models\DraftRanking;
use Inertia\Inertia;

class DraftRankingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $draftRankings = DraftRanking::query()
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->with([
                'player' => [
                    'position',
                    'team',
                ],
            ])
            ->orderBy('rank', 'asc')
            ->get();

        return Inertia::render('rankings/index', [
            'draftRankings' => $draftRankings ?? [],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(DraftRanking $draftRanking)
    {
        return Inertia::render('rankings/show', [
            'draftRanking' => $draftRanking,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        return Inertia::render('rankings/create');
    }

    /**
     * Update the specified resource in storage.
     */
    public function edit(DraftRanking $draftRanking)
    {
        return Inertia::render('rankings/edit', [
            'draftRanking' => $draftRanking,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DraftRanking $draftRanking)
    {
        $draftRanking->delete();
        return redirect()->route('rankings/index');
    }
}
