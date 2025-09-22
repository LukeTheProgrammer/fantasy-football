<?php

namespace App\Console\Commands\Scrapers;

use App\Enums\DataSourceEnum;
use App\Facades\Action;
use App\Facades\Scraper;
use App\Models\Position;
use App\Models\Player;
use App\Services\Espn\EspnConstants;
use App\Services\Scrapers\Resources\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use function Laravel\Prompts\select;

class GetEspnRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrapers:espn:get-roster
        { --a|all   : Scrapes all teams }
        { --q|quiet : No output         }
        { team?     : Team name         }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team from the ESPN API.';

    protected ?Collection $positions = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->positions = Position::all()->keyBy('abbreviation');

        if ($this->option('all')) {
            foreach (Espn::TEAMS as $teamName => $team) {
                $this->getRoster($teamName);
            }
        } else {
            $teamName = $this->argument('team') ?? select(
                label: 'Team',
                options: array_keys(Espn::TEAMS),
                default: null,
            );

            $this->getRoster($teamName);
        }

        return Command::SUCCESS;
    }

    private function getRoster(string $teamName)
    {
        if (! $this->option('quiet')) {
            $this->info("Loading rosters for {$teamName}");
        }

        $scraper = Scraper::scraper(DataSourceEnum::ESPN->value);
        $data = $scraper->getTeamRoster($teamName);

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

            $pos = $this->positions->get($ffPos->value);

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
    }
}
