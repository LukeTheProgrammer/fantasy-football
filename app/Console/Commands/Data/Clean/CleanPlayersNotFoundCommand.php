<?php

namespace App\Console\Commands\Data\Clean;

use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerMissing;
use App\Services\FantasyPros\Formatters\ProjectionFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\multiselect;

class CleanPlayersNotFoundCommand extends Command
{
    protected $signature = 'data:clean:players-not-found';

    protected $description = 'Clean players not found';

    protected $playerProps = [
        'id',
        'espn_id',
        'pfr_id',
        'fp_id',
        'position_id',
        'team_id',
        'first_name',
        'last_name',
        'full_name',
    ];

    public function handle(): void
    {
        PlayerMissing::all()->each(fn ($pn) => $this->processPlayerMissing($pn));
    }

    protected function processPlayerMissing(PlayerMissing $pn)
    {
        $this->info('Resolving ' . $pn->id);

        if ($pn->source_class === ProjectionFormatter::class) {
            return $this->resolveFantasyProsFormatter($pn);
        }

        $data = is_array($pn->source_data) ? $pn->source_data : json_decode($pn->source_data, true);
        $opts = [];
        $rows = [];

        foreach ($data as $k => $v) {
            $opts[$k] = $k . ' => ' . $v;
            $rows[] = [$k, $v];
        }

        $this->table(['Prop', 'Value'], $rows);

        dd('dafuq');

        ksort($opts);

        $selected = multiselect(
            label: 'Select properties to use in the search',
            options: $opts,
            scroll: 15,
        );

        $map = [];

        foreach ($selected as $prop) {
            $map[$prop] = select(
                label: 'Select player property ' . $prop . ' maps to',
                options: $this->playerProps,
                scroll: 15,
            );
        }

        $player = $this->playerSearch($data, $map);

        if ($player instanceof Player) {
            $this->updatePlayer($player, $map, $data);
            $pn->delete();
        }
    }

    protected function playerSearch(array $data, array $map): ?Player
    {
        foreach ($map as $dataKey => $playerKey) {
            $val = Arr::get($data, $dataKey);
            $this->info('Searching for ' . $playerKey . ' => ' . $val);
            $players = Player::where($playerKey, '=', $val)->get();

            if ($players->count() == 1) {
                $player = $players->first();
                $label = $this->playerLabel($player);

                if (confirm('Is this the correct player? ' . $label)) {
                    return $player;
                }
            }

            if ($players->count() > 1) {
                $player = $this->selectPlayer($players);

                if ($player instanceof Player) {
                    return $player;
                }
            }
        }

        return null;
    }

    protected function selectPlayer(Collection $players): ?Player
    {
        $opts = [
            '_NULL_' => 'None',
        ];

        foreach ($players as $player) {
            $opts[$player->id] = $this->playerLabel($player);
        }

        $pid = select(
            label: 'Select player',
            options: $opts,
            scroll: 15,
        );

        if ($pid === '_NULL_') {
            return null;
        }

        return $players->where('id', $pid)->first();
    }

    protected function playerLabel(Player $player)
    {
        return '[' . $player->id . '] ' . $player->full_name . ' ' . $player->position_id . ' ' . $player->team_id;
    }

    protected function updatePlayer(Player $player, array $map, array $data)
    {
        $updateOpts = ['_NULL_' => 'None'];
        $nameOpts = ['_NULL_' => 'None'];

        foreach ($map as $dataKey => $playerKey) {
            $val = Arr::get($data, $dataKey);
            $up = $playerKey . ' => ' . $val;

            $nameOpts[$val] = $val;
            $updateOpts[$up] = $up;
        }

        $player->update($updateOpts);

        if (confirm('Create player alias?')) {
            $name = select(
                label: 'Select player alias name',
                options: $nameOpts,
                scroll: 15,
            );

            if ($name === '_NULL_') {
                return;
            }

            PlayerAlias::create([
                'player_id' => $player->id,
                'name' => $name,
            ]);
        }
    }

    protected function resolveFantasyProsFormatter(PlayerMissing $pn)
    {
        $data = is_array($pn->source_data) ? $pn->source_data : json_decode($pn->source_data, true);

        $this->table(['Prop', 'Value'], [
            ['FP ID', Arr::get($data, 'player_id')],
            ['Name', Arr::get($data, 'player_name')],
            ['Position', Arr::get($data, 'player_position_id')],
            ['Team', Arr::get($data, 'player_team_id')],
        ]);

        $teamId = match (Arr::get($data, 'player_team_id')) {
            'ARI' => NFLTeams::ARI->value,
            'JAC' => NFLTeams::JAX->value,
            'WAS' => NFLTeams::WSH->value,
            default => NFLTeams::from(Arr::get($data, 'player_team_id'))->value,
        };

        $playerData = [
            'fp_id'       => Arr::get($data, 'player_id'),
            'full_name'   => Arr::get($data, 'player_name'),
            'position_id' => Arr::get($data, 'player_position_id'),
            'team_id'     => $teamId,
        ];

        $pq = Player::nameLike($playerData['full_name']);

        if ($pq->count() > 1) {
            $player = $this->selectPlayer($pq->get());

            if ($player instanceof Player) {
                $this->resolvePlayerMissing($pn, $player, [
                    'fp_id' => $playerData['fp_id'],
                ]);
                return;
            }
        }

        if ($pq->count() > 0) {
            $player = $pq->first();

            if (confirm('Is this the correct player? ' . $this->playerLabel($player))) {
                $this->resolvePlayerMissing($pn, $player, [
                    'fp_id' => $playerData['fp_id'],
                ]);
                return;
            }
        }

        if ($teamId !== 'FA') {
            $pq = Player::forTeam($teamId)->forPosition($playerData['position_id']);

            if ($pq->count() > 1) {
                $player = $this->selectPlayer($pq->get());

                if ($player instanceof Player) {
                    $this->resolvePlayerMissing($pn, $player, [
                        'fp_id' => $playerData['fp_id'],
                    ]);
                    return;
                }
            }

            if ($pq->count() > 0) {
                $player = $pq->first();

                if (confirm('Is this the correct player? ' . $this->playerLabel($player))) {
                    $this->resolvePlayerMissing($pn, $player, [
                        'fp_id' => $playerData['fp_id'],
                    ]);
                    return;
                }
            }
        }

        if (confirm('No match found. Create player?')) {
            $name = explode(' ', Arr::get($data, 'player_name'));

            Action::model(Player::class)->upsert([
                'fp_id'       => Arr::get($data, 'player_id'),
                'position_id' => Arr::get($data, 'player_position_id'),
                'team_id'     => $teamId,
                'first_name'  => $name[0],
                'last_name'   => $name[1],
                'full_name'   => Arr::get($data, 'player_name'),
            ]);
            $pn->delete();
        }
    }

    private function resolvePlayerMissing(PlayerMissing $pn, Player $player, array $update)
    {
        if (! empty($update)) {
            $player->update($update);
        }

        $pn->delete();
    }
}
