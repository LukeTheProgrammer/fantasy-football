<?php

namespace Database\Seeders;

use App\Models\NflGame;
use App\Models\Season;
use App\Models\Week;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSeason(2024);
        $this->createSeason(2025);
        $this->createSeason(2026);

        $this->markCurrentWeek();
    }

    private function createSeason(int $season): void
    {
        $season = Season::updateOrCreate(
            ['id' => $season],
            ['is_current' => (string) $season === date('Y')]
        );

        for ($week = 1; $week <= 18; $week++) {
            $games = NflGame::forSeason($season)
                ->forWeek($week)
                ->whereNotNull('starts_at')
                ->orderBy('starts_at')
                ->get();

            $first = $games->first();
            $last = $games->last();

            Week::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'week' => $week,
                ],
                [
                    'starts_at' => $first?->starts_at,
                    'ends_at' => $last?->starts_at,
                ]
            );
        }
    }

    /**
     * Exactly one week is ever current: the earliest not yet finished, in the
     * current season. Any previously flagged week is cleared first.
     */
    private function markCurrentWeek(): void
    {
        Week::where('is_current', true)->update(['is_current' => false]);

        $current = Season::where('is_current', true)->first();

        if (! $current instanceof Season) {
            return;
        }

        Week::where('season_id', $current->id)
            ->whereDate('ends_at', '>=', date('Y-m-d'))
            ->orderBy('week')
            ->limit(1)
            ->update(['is_current' => true]);
    }
}
