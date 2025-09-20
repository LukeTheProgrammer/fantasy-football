<?php

namespace App\Console\Commands\Espn\NFL;

use App\Models\NflGame;
use App\Models\Team;
use App\Services\Espn\Data\NFL\EventData;
use App\Services\Espn\Data\NFL\ResourceTeamScheduleData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class LoadTeamSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:load:team-schedules
        { --a|all       : Load all rosters    }
        { --r|raw       : Return raw response }
        { --q|quiet     : Do not show output  }
        { espn_team_id? : The ESPN Team ID    }
        { year?         : Which year to pull  }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team schedules from a file.';

    protected ?int $year = null;

    protected int|string|null $teamId = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->year = $this->argument('year') ?? select('Which year to pull', [2025, 2024]);

        if ($this->option('all')) {
            return $this->loadAllRosters();
        }

        $this->teamId = $this->argument('espn_team_id');

        if ($this->teamId) {
            return $this->loadTeamRoster();
        } else {
            $this->teamId = select('Which team to pull', Team::all()->pluck('name', 'espn_id')->toArray());
            $this->loadTeamRoster();
        }
    }

    protected function loadAllRosters()
    {
        Team::all()->each(function (Team $team) {
            $this->teamId = $team->espn_id;
            $this->loadTeamRoster();
        });
    }

    protected function loadTeamRoster()
    {
        $team = Team::where('espn_id', '=', $this->teamId)->first();
        $path = $this->getFilePath();

        if (! $this->option('quiet')) {
            $this->info('Loading Schedule for ' . $team->abbreviation . ' [' . $this->teamId . '] ' . $path . PHP_EOL);
        }

        if (! file_exists($path)) {
            $this->error('Schedule file does not exist: ' . $path);
            $this->call('espn:nfl:get:team-schedule', ['espn_team_id' => $this->teamId, '--raw' => true]);
        }

        $this->loadSchedule($path);
    }

    protected function getFilePath()
    {
        $parts = [
            'data',
            'espn',
            'nfl',
            'team-schedules',
            $this->option('raw') ? 'raw' : 'formatted',
            'team-schedule-' . $this->teamId . '-' . $this->year . '.json'
        ];

        return storage_path(implode('/', $parts));
    }

    protected function loadSchedule(string $path)
    {
        $data = file_get_contents($path);
        $scheduleData = json_decode($data, true);

        $schedule = ResourceTeamScheduleData::from($scheduleData);

        if (! $this->option('quiet')) {
            $bar = $this->output->createProgressBar($schedule->events->count());
            $bar->start();
        } else {
            $bar = null;
        }

        $schedule->events->each(function (EventData $event) use ($bar, $schedule) {
            if (! $this->option('quiet')) {
                $bar->advance();
            }

            /** @var CompetitionData $competition */
            $competition = $event->competitions->first();

            /** @var CompetitorData $homeTeam */
            $homeTeam = $competition->competitors->firstWhere('homeAway', 'home');

            /** @var CompetitorData $awayTeam */
            $awayTeam = $competition->competitors->firstWhere('homeAway', 'away');

            $this->upsert([
                'espn_id'      => $event->id,
                'home_team_id' => Team::forEspnId($homeTeam->team->id)->first()->id,
                'away_team_id' => Team::forEspnId($awayTeam->team->id)->first()->id,
                'year'         => $event->season->year,
                'week'         => $event->week->number,
                'start_time'   => $event->date,
                'home_score'   => $homeTeam->score->value,
                'away_score'   => $awayTeam->score->value,
                'is_completed' => $competition->status->type->completed ?? false,
                'is_playoff'   => Str::lower($event->seasonType->abbreviation) !== 'reg',
            ]);
        });

        if (! $this->option('quiet')) {
            $bar->finish();
            echo PHP_EOL . PHP_EOL;
        }
    }

    protected function upsert(array $data): NflGame
    {
        return NflGame::updateOrCreate(
            ['espn_id' => $data['espn_id']],
            $data
        );
    }
}
