<?php

namespace App\Console\Commands\Espn\NFL;

use App\Models\NflGame;
use App\Models\Team;
use App\Services\Espn\Data\NFL\EventData;
use App\Services\Espn\Data\NFL\ResourceTeamScheduleData;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LoadTeamSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:load:team-schedules
        { --a|all       : Load all rosters   }
        { --q|quiet     : Do not show output }
        { espn_team_id? : The ESPN Team ID   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team schedules from a file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->loadAllRosters();
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            return $this->loadTeamRoster($teamId);
        }
    }

    protected function loadAllRosters()
    {
        Team::all()->each(function (Team $team) {
            $this->loadTeamRoster($team);
        });
    }

    protected function loadTeamRoster(int|string|Team $teamArg)
    {
        $team = ($teamArg instanceof Team) ? $teamArg : Team::where('espn_id', '=', $teamArg)->first();

        $teamId = $team->espn_id;

        if (! $this->option('quiet')) {
            $this->info('Loading Schedule for ' . $team->abbreviation . ' [' . $teamId . ']' . PHP_EOL);
        }

        $path = storage_path('data/espn/nfl/team-schedules/raw/team-schedule-' . $teamId . '.json');

        if (! file_exists($path)) {
            $this->error('Schedule file does not exist: ' . $path);
            $this->call('espn:nfl:get:team-schedule', ['espn_team_id' => $teamId]);
        }

        $this->loadSchedule($team, $path);
    }

    protected function loadSchedule(Team $team, string $path)
    {
        $data = file_get_contents($path);
        $scheduleData = json_decode($data, true);

        $schedule = ResourceTeamScheduleData::from($scheduleData);

        if (! $this->option('quiet')) {
            $bar = $this->output->createProgressBar($schedule->events->count());
            $bar->start();
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
                'year'         => $schedule->season->year,
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
