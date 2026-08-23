<?php

namespace App\Services\Imports\Drivers\Projections;

use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Facades\FantasyPros;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerMissing;
use App\Models\PlayerProjection;
use App\Models\Team;
use App\Services\FantasyPros\Resources\ProjectionsResource;
use Exception;
use Illuminate\Support\Arr;

class FantasyProsProjectionsDriver extends BaseProjectionsDriver
{
    protected ?ProjectionsResource $fp = null;

    public function __construct()
    {
        $this->config = collect([
            'week' => null,
            'season' => null,
        ]);
    }

    public function setUp(array $config = [])
    {
        $this->config->each(function ($defaultVal, $key) use ($config) {
            $val = Arr::get($config, $key, $defaultVal);
            $this->config->put($key, $val);
        });

        $this->fp = FantasyPros::projections();


        $errors = [];

        $this->config->each(function ($config, $key) use (&$errors) {
            if (! $config) {
                $errors[] = 'Missing ' . $key;
            }
        });

        if ($errors) {
            throw new Exception('Missing required config ' . implode(', ', $errors));
        }
    }

    public function load()
    {
        foreach ($this->fp->sources as $label => $url) {
            $data = $this->fp->getProjection(
                $label,
                $this->config->get('season'),
                $this->config->get('week')
            );

            if (empty($data)) {
                continue;
            }

            foreach ($data as $player) {
                $this->save($label, $player);
            }
        }
    }

    public function save(string $label, array $playerData)
    {
        if (Arr::get($playerData, 'player_team_id') === 'FA') {
            return;
        }

        $update = [
            'fp_projected_points' => null,
            'fp_pos_rank' => null,
            'fp_pos_rank_min' => null,
            'fp_pos_rank_max' => null,
            'fp_pos_rank_avg' => null,
            'fp_pos_rank_std' => null,

            'fp_half_projected_points' => null,
            'fp_half_pos_rank' => null,
            'fp_half_pos_rank_min' => null,
            'fp_half_pos_rank_max' => null,
            'fp_half_pos_rank_avg' => null,
            'fp_half_pos_rank_std' => null,

            'fp_ppr_projected_points' => null,
            'fp_ppr_pos_rank' => null,
            'fp_ppr_pos_rank_min' => null,
            'fp_ppr_pos_rank_max' => null,
            'fp_ppr_pos_rank_avg' => null,
            'fp_ppr_pos_rank_std' => null,

            'fp_2qb_projected_points' => null,
            'fp_2qb_pos_rank' => null,
            'fp_2qb_pos_rank_min' => null,
            'fp_2qb_pos_rank_max' => null,
            'fp_2qb_pos_rank_avg' => null,
            'fp_2qb_pos_rank_std' => null,
        ];

        $r2p_pts  = floatval(Arr::get($playerData, 'r2p_pts'));
        $rank_ecr = intval(Arr::get($playerData, 'rank_ecr'));
        $rank_min = intval(Arr::get($playerData, 'rank_min'));
        $rank_max = intval(Arr::get($playerData, 'rank_max'));
        $rank_ave = floatval(Arr::get($playerData, 'rank_ave'));
        $rank_std = floatval(Arr::get($playerData, 'rank_std'));

        if ($label === '2-qb') {
            $update['fp_2qb_projected_points'] = $r2p_pts;
            $update['fp_2qb_pos_rank']         = $rank_ecr;
            $update['fp_2qb_pos_rank_min']     = $rank_min;
            $update['fp_2qb_pos_rank_max']     = $rank_max;
            $update['fp_2qb_pos_rank_avg']     = $rank_ave;
            $update['fp_2qb_pos_rank_std']     = $rank_std;

        } else if (in_array($label, ['half-rb', 'half-wr', 'half-te'])) {
            $update['fp_half_projected_points'] = $r2p_pts;
            $update['fp_half_pos_rank']         = $rank_ecr;
            $update['fp_half_pos_rank_min']     = $rank_min;
            $update['fp_half_pos_rank_max']     = $rank_max;
            $update['fp_half_pos_rank_avg']     = $rank_ave;
            $update['fp_half_pos_rank_std']     = $rank_std;

        } else if (in_array($label, ['ppr-rb', 'ppr-wr', 'ppr-te'])) {
            $update['fp_ppr_projected_points'] = $r2p_pts;
            $update['fp_ppr_pos_rank']         = $rank_ecr;
            $update['fp_ppr_pos_rank_min']     = $rank_min;
            $update['fp_ppr_pos_rank_max']     = $rank_max;
            $update['fp_ppr_pos_rank_avg']     = $rank_ave;
            $update['fp_ppr_pos_rank_std']     = $rank_std;

        } else {
            $update['fp_projected_points'] = $r2p_pts;
            $update['fp_pos_rank']         = $rank_ecr;
            $update['fp_pos_rank_min']     = $rank_min;
            $update['fp_pos_rank_max']     = $rank_max;
            $update['fp_pos_rank_avg']     = $rank_ave;
            $update['fp_pos_rank_std']     = $rank_std;
        }

        $update = array_filter($update);

        $player = $this->findPlayer($playerData);

        if (! $player instanceof Player) {
            // dd(['Player Not Found' => $playerData]);
            $this->addError('Player Not Found', $playerData, $update);
            return;
        }

        if (! $player->team instanceof Team) {
            // dd('Player has no team', $player->toArray());
            $this->addError('Player has no team', $playerData, $update);
            return null;
        }

        $nflGame = $this->findNflGame($player);

        // if (! $nflGame instanceof NFLGame) {
        //     // dd(['NflGame Not Found' => $playerData]);
        //     $this->addError('NflGame Not Found', $playerData, $update);
        //     return;
        // }

        $find = [
            'player_id' => $player->id,
            'season' => $this->config->get('season'),
            'week' => $this->config->get('week'),
        ];

        $update['nfl_game_id'] = $nflGame?->id ?? null;

        PlayerProjection::updateOrCreate($find, $update);
    }

    public function findPlayer(array $data)
    {
        $player = Player::fpId(Arr::get($data, 'player_id'))->first();

        if (! $player instanceof Player) {
            $pq = Player::where('full_name', '=', Arr::get($data, 'player_name'));

            if ($pq->count() === 1) {
                $player = $pq->first();
            }
        }

        if (! $player instanceof Player) {
            $paq = PlayerAlias::where('name', '=', Arr::get($data, 'player_name'));

            if ($paq->count() === 1) {
                $player = $paq->first()->player;
            }
        }

        if (! $player instanceof Player) {
            $this->addError('Player Not Found', $data, []);
            Action::model(PlayerMissing::class)->upsert($data, get_called_class(), [
                'unique_id_key' => 'fp_id',
                'unique_id_value' => Arr::get($data, 'player_id'),
                'name' => Arr::get($data, 'player_name'),
                'position_id' => Arr::get($data, 'player_position_id'),
                'team_id' => Arr::get($data, 'player_team_id'),
            ]);
            return null;
        }

        return $player;
    }

    public function getTeamId(?string $team = null)
    {
        if (empty($team)) {
            return null;
        }

        return match ($team) {
            'ARI' => NFLTeams::ARI->value,
            'JAC' => NFLTeams::JAX->value,
            'WAS' => NFLTeams::WSH->value,
            default => NFLTeams::from($team)->value,
        };
    }

    public function findNflGame(Player $player)
    {
        if ($player->team_id == 'FA' || ! $player->team instanceof Team) {
            // dd('Player has no team', $player->toArray());
            return null;
        }

        $q = NflGame::query()
            ->forTeam($player->team)
            ->forSeason($this->config->get('season'))
            ->forWeek($this->config->get('week'));

        $game = $q->first();

        if (! $game instanceof NflGame ) {
            dd('Game Not Found', $player->toArray(), $q->toSql(), $q->getBindings());
        }

        return $game;
    }

    public function addError(string $type, array $playerData, array $formattedData)
    {
        $this->errors[] = [
            'type' => $type,
            'playerData' => $playerData,
            'formattedData' => $formattedData,
        ];
    }

    public function tearDown()
    {
        //
    }
}
