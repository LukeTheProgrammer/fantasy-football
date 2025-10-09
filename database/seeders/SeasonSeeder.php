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

        Week::whereDate('ends_at', '>=', date('Y-m-d'))->limit(1)->update(['is_current' => true]);
    }
}
