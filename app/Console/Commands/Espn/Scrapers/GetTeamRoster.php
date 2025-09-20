<?php

namespace App\Console\Commands\Espn\Scrapers;

use App\Facades\Action;
use App\Facades\Espn;
use App\Models\Position;
use App\Models\Player;
use App\Services\Espn\Resources\Scrapers\NflTeamRoster;
use App\Services\Espn\EspnConstants;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use function Laravel\Prompts\select;

class GetTeamRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:scrape:nfl-roster
        { team? : Team name }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teamName = $this->argument('team') ?? select(
            label: 'Team',
            options: array_keys(NflTeamRoster::TEAMS),
            default: null,
        );

        $positions = Position::all()->keyBy('abbreviation');

        $data = Espn::scrapers()->getRoster($teamName);

        $teamId = $data['team']->id;

        foreach ($data['roster'] as $player) {
            $fullName = Arr::get($player, 'name');
            $si = strpos($fullName, ' ');
            $firstName = substr($fullName, 0, $si);
            $lastName = substr($fullName, $si + 1);

            $espnPos = Arr::get($player, 'position');
            if (! $espnPos) {
                continue;
            }

            $ffPos = Arr::get(EspnConstants::POSITION_MAP, $espnPos);
            if (! $ffPos) {
                continue;
            }

            $pos = $positions->get($ffPos->value);

            if (! $pos instanceof Position) {
                $this->error('Position not found.');
                dd($player);
            }

            Action::model(Player::class)->upsert([
                'espn_id'       => $player['id'],
                'position_id'   => $pos->id,
                'team_id'       => $teamId,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'full_name'     => $fullName,
                'jersey_number' => Arr::get($player, 'jersey'),
                'height'        => Arr::get($player, 'height'),
                'weight'        => Arr::get($player, 'weight'),
                'headshot'      => Arr::get($player, 'headshot'),
            ]);
        }

        return Command::SUCCESS;
    }
}
