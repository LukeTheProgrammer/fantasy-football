<?php

namespace App\Http\Controllers;

use App\Models\DraftRanking;
use App\Models\Season;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DraftRankingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $season = Season::current()->first()?->id ?? (int) date('Y');

        // One scoring format at a time, or every player appears once per format.
        $format = $this->format(request(), $season);

        $draftRankings = DraftRanking::query()
            ->latestRanking($season)
            ->forFormat($format['ppr'], $format['superflex'], $format['type'])
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

        return Inertia::render('rankings/RankingsIndexPage', [
            'draftRankings' => $draftRankings ?? [],
            'format'        => $format,
            'formats'       => $this->formats($season),
        ]);
    }

    /**
     * The format to show, from the request when it names one, otherwise the
     * first format held for the season.
     *
     * @return array<string, mixed>
     */
    private function format(Request $request, int $season): array
    {
        $formats = $this->formats($season);

        if (empty($formats)) {
            return [
                'key'       => 'redraft-0-0',
                'label'     => 'Standard',
                'ppr'       => 0.0,
                'superflex' => false,
                'type'      => 'redraft',
            ];
        }

        $requested = $request->query('format');

        return collect($formats)->firstWhere('key', $requested) ?? $formats[0];
    }

    /**
     * Every scoring format rankings were imported for.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formats(int $season): array
    {
        return DraftRanking::query()
            ->where('season', $season)
            ->select(['ppr', 'superflex', 'type'])
            ->distinct()
            ->get()
            ->map(fn (DraftRanking $ranking) => $this->describeFormat(
                (float) $ranking->ppr,
                (bool) $ranking->superflex,
                $ranking->type
            ))
            // Redraft before dynasty, plain scoring before superflex, then by
            // reception value, so the list reads the way a drafter thinks.
            ->sortBy(fn ($format) => sprintf(
                '%d-%d-%03d',
                $format['type'] === 'dynasty' ? 1 : 0,
                $format['superflex'] ? 1 : 0,
                (int) ($format['ppr'] * 10)
            ))
            ->values()
            ->all();
    }

    /**
     * A format as the rankings page needs it: something to key an option by,
     * and something to call it.
     *
     * @return array<string, mixed>
     */
    private function describeFormat(float $ppr, bool $superflex, string $type): array
    {
        $scoring = match ($ppr) {
            1.0     => 'PPR',
            0.5     => 'Half PPR',
            default => 'Standard',
        };

        $label = $superflex ? 'Superflex' : $scoring;

        if ($type === 'dynasty') {
            $label = 'Dynasty ' . $label;
        }

        return [
            'key'       => implode('-', [$type, $ppr, $superflex ? 1 : 0]),
            'label'     => $label,
            'ppr'       => $ppr,
            'superflex' => $superflex,
            'type'      => $type,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(DraftRanking $draftRanking)
    {
        return Inertia::render('rankings/ShowRankingPage', [
            'draftRanking' => $draftRanking,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        return Inertia::render('rankings/CreateRankingPage');
    }

    /**
     * Update the specified resource in storage.
     */
    public function edit(DraftRanking $draftRanking)
    {
        return Inertia::render('rankings/EditRankingPage', [
            'draftRanking' => $draftRanking,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DraftRanking $draftRanking)
    {
        $draftRanking->delete();

        return redirect()->route('rankings.index');
    }
}
