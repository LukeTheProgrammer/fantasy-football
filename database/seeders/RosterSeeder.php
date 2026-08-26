<?php

namespace Database\Seeders;

use App\Enums\NFLPositions;
use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\Position;
use App\Models\Team;
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

            if (!$player instanceof Player) {
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
        Artisan::call('import:nfl:roster', [
            '--espn' => true,
            'season' => 2025,
        ]);
    }

    private function pfr()
    {
        Artisan::call('import:nfl:roster', [
            'season' => 2025,
        ]);
    }
}
