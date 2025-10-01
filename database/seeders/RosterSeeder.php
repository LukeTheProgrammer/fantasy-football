<?php

namespace Database\Seeders;

use App\Enums\NFLPositions;
use App\Facades\Action;
use App\Models\Position;
use App\Models\Team;
use App\Models\Player;
use App\Models\PlayerTeam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RosterSeeder extends Seeder
{
    public function run(): void
    {
        $this->assignDSTs();
        $this->espn();
        $this->pfr();
    }

    private function assignDSTs()
    {
        $dst = Position::forAbbreviation(NFLPositions::DST->value)->first();

        Team::noFA()->get()->each(function (Team $team) use ($dst) {
            $player = Player::espnId($team->espn_id)->forPosition($dst)->first();

            if (! $player instanceof Player) {
                dd([
                    $team->toArray(),
                    $dst->toArray(),
                ]);
            }

            Action::model(PlayerTeam::class)->upsert($player, $team);
        });
    }

    private function espn()
    {
        Artisan::call('scrapers:espn:get-roster', [
            '--all' => true,
            '--quiet' => true,
        ]);
    }

    private function pfr()
    {
        Artisan::call('pfr:load:rosters', [
            '--all' => true,
            'year'  => 2025,
        ]);

        // Artisan::call('pfr:load:rosters', [
        //     '--all' => true,
        //     'year'  => 2024,
        // ]);
    }
}
