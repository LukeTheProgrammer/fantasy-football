<?php

namespace App\Services\CBS\Formatters;

use App\Models\Player;
use App\Models\NflGame;
use App\Models\Team;
use App\Services\CBS\Data\NFL\Schedule\ScheduleResourceData;
use App\Services\CBS\Data\NFL\Schedule\EventData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NFLScheduleFormatter
{
    protected ?Collection $teams = null;

    public function __construct(protected ?ScheduleResourceData $scheduleData = null)
    {
        $this->teams = Team::noFA()->get()->keyBy('espn_id');
    }

    public static function from(array|ScheduleResourceData $data)
    {
        if (! $data instanceof ScheduleResourceData) {
            $data = ScheduleResourceData::from($data);
        }

        $formatter = new NFLScheduleFormatter($data);

        return $formatter->format();
    }

    public function format()
    {
        return $this->scheduleData->events->map(
            fn (EventData $event) => $this->formatEvent($event)
        )->filter();
    }

    public function formatEvent(EventData $event)
    {
        $competition = $event->competitions->first();

        $data = [
            'espn_id'      => $event->id,
            'season'       => $event->season->year,
            'week'         => $event->week->number,
            'starts_at'    => Carbon::parse($event->date)->toDateTimeString(),
            'is_completed' => $competition->status->type->completed,
            'is_playoff'   => false,
            'home_team_id' => null,
            'away_team_id' => null,
            'home_score'   => null,
            'away_score'   => null,
        ];

        $competition->competitors->each(function ($competitor) use (&$data) {
            $team = $this->teams->get($competitor->team->id);

            if (! $team instanceof Team) {
                Log::error('Team not found', $competitor->toArray());
                return true;
            }

            if ($competitor->homeAway === 'home') {
                $data['home_team_id'] = $team->id;
                $data['home_score'] = $competitor->score->value;
            } else {
                $data['away_team_id'] = $team->id;
                $data['away_score'] = $competitor->score->value;
            }
        });

        return $data;
    }
}
