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

    private function createSeason(int $year): void
    {
        $games = NflGame::forYear($year)->orderBy('week')->get()->groupBy('week');

        $season = Season::updateOrCreate(
            ['id' => $year],
            ['is_current' => (string) $year === date('Y')]
        );

        foreach ($games as $week => $weekGames) {
            $first = $weekGames->sortBy('starts_at')->first();

            Week::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'week' => $week,
                ],
                [
                    'starts_at' => $first?->starts_at,
                ]
            );
        }

        Week::whereDate('starts_at', '>=', date('Y-m-d'))->limit(1)->update(['is_current' => true]);
    }
}
