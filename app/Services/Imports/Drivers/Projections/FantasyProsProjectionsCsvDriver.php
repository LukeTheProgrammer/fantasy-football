<?php

namespace App\Services\Imports\Drivers\Projections;

use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\PlayerProjection;
use App\Models\Position;
use App\Models\Team;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class FantasyProsProjectionsCsvDriver extends BaseProjectionsDriver
{
    public mixed $fp;

    public ?Position $position = null;

    public function __construct()
    {
        $this->config = collect([
            'filePath' => null,
            'position' => null,
            'ppr'      => null,
            'week'     => null,
            'season'   => null,
        ]);
    }

    public function setUp(array $config = [])
    {
        $this->config->each(function ($defaultVal, $key) use ($config) {
            $val = Arr::get($config, $key, $defaultVal);
            $this->config->put($key, $val);
        });

        $storagePath = storage_path();
        $configPath = $this->config->get('filePath');

        $filePath = (str_starts_with($configPath, $storagePath))
            ? $configPath
            : $storagePath . $configPath;

        if (! file_exists($filePath)) {
            // dd($this->config->toArray());
            throw new Exception('File does not exist ' . $filePath);
        }

        $errors = [];

        $this->config->each(function ($config, $key) use (&$errors) {
            if (! $config) {
                $errors[] = 'Missing ' . $key;
            }
        });

        if ($errors) {
            throw new Exception('Missing required config ' . implode(', ', $errors));
        }

        $this->fp = fopen($filePath, 'r');

        $this->fileProps = fgetcsv($this->fp);
    }

    public function load()
    {
        while (($line = fgetcsv($this->fp)) !== false) {
            $data = array_combine($this->fileProps, $line);
            $this->save($data);
        }
    }

    public function save(array $fileData)
    {
        $data = $this->formatData($fileData);

        $player = $this->findPlayer($data);

        if (! $player instanceof Player) {
            $this->addPlayerMissingError($fileData, $data);
            return;
        }

        $nflGame = $this->findNflGame($player);

        if (! $nflGame instanceof NFLGame) {
            $this->addNflGameNotFoundError($fileData, $data);
            return;
        }

        $find = [
            'player_id' => $player->id,
            'nfl_game_id' => $nflGame->id,
        ];

        $update = array_filter([
            'fp_projected_points' => floatVal(Arr::get($data, 'points')),
            'fp_position_rank'    => intVal(Arr::get($data, 'rank')),

            'fp_projected_points' => 0,
            'fp_pos_rank' => 0,
            'fp_pos_rank_min' => 0,
            'fp_pos_rank_max' => 0,
            'fp_pos_rank_avg' => 0,
            'fp_pos_rank_std' => 0,

            'fp_half_projected_points' => 0,
            'fp_half_pos_rank' => 0,
            'fp_half_pos_rank_min' => 0,
            'fp_half_pos_rank_max' => 0,
            'fp_half_pos_rank_avg' => 0,
            'fp_half_pos_rank_std' => 0,

            'fp_ppr_projected_points' => 0,
            'fp_ppr_pos_rank' => 0,
            'fp_ppr_pos_rank_min' => 0,
            'fp_ppr_pos_rank_max' => 0,
            'fp_ppr_pos_rank_avg' => 0,
            'fp_ppr_pos_rank_std' => 0,

            'fp_2qb_projected_points' => 0,
            'fp_2qb_pos_rank' => 0,
            'fp_2qb_pos_rank_min' => 0,
            'fp_2qb_pos_rank_max' => 0,
            'fp_2qb_pos_rank_avg' => 0,
            'fp_2qb_pos_rank_std' => 0,
        ]);


        PlayerProjection::updateOrCreate($find, $update);
    }

    public function formatData(array $data)
    {
        $newData = [];

        foreach ($this->dataMap as $dbProp => $fileProp) {
            $newData[$dbProp] = Arr::get($data, $fileProp);
        }

        return $newData;
    }

    public function findPlayer(array $data)
    {
        $player = Player::where('full_name', Arr::get($data, 'player_name'))->first();

        if ($player instanceof Player) {
            return $player;
        }

        $alias = PlayerAlias::forName(Arr::get($data, 'player_name'))->first();

        if ($alias instanceof PlayerAlias) {
            return $alias->player;
        }

        return false;
    }

    public function findNflGame(Player $player)
    {
        if (! $player->team instanceof Team) {
            return null;
        }

        return NflGame::query()
            ->forTeam($player->team)
            ->forSeason($this->config->get('season'))
            ->forWeek($this->config->get('week'))
            ->select('nfl_games.*')
            ->first();
    }

    public function tearDown()
    {
        fclose($this->fp);
    }

    public function addPlayerMissingError(array $fileData, array $formattedData)
    {
        $this->errors[] = [
            'type' => 'Player Not Found',
            'fileData' => $fileData,
            'formattedData' => $formattedData,
        ];
    }

    public function addNflGameNotFoundError(array $fileData, array $formattedData)
    {
        $this->errors[] = [
            'type' => 'NflGame Not Found',
            'fileData' => $fileData,
            'formattedData' => $formattedData,
        ];
    }
}
