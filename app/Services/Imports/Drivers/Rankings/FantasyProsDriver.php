<?php

namespace App\Services\Imports\Drivers\Rankings;

use App\Models\DraftRanking;
use App\Models\Player;
use App\Services\Imports\Drivers\BaseImportDriver;
use Exception;
use Illuminate\Support\Arr;

class FantasyProsDriver extends BaseImportDriver
{
    public $fp;

    public array $headers = [];

    public function __construct(public string $filePath, public string $fileType = 'csv')
    {
        //
    }

    public function import()
    {
        $this->setUp();

        $this->loadFile();

        $this->tearDown();
    }

    public function setUp()
    {
        if (!file_exists($this->filePath)) {
            throw new Exception('File does not exist ' . $this->filePath);
        }
    }

    public function loadFile()
    {
        $this->fp = fopen($this->filePath, 'r');

        $this->headers = fgetcsv($this->fp);

        // Loads headers
        // $this->getNextLine();
    }

    public function getNextLine()
    {
        if (($line = fgetcsv($this->fp)) !== false) {
            $data = array_combine($this->headers, $line);
            return $data;
        }

        return false;
    }

    public function saveRanking(Player $player, array $data, ?array $fields = null)
    {
        $data = $this->prepareRankingData($data, $fields);

        DraftRanking::updateOrCreate([
            'player_id' => $player->id,
            'year' => date('Y'),
        ], $data);
    }

    public function prepareRankingData(array $data, ?array $fields = null)
    {
        $fields ??= [
            'RK',
            'TIERS',
            'AVG',
            'ADV',
            'ECR vs ADP',
        ];

        $cleanData = [];

        foreach ($data as $key => $value) {
            if (! in_array($key, $fields)) {
                continue;
            }

            $cleanData[$key] = match ($key) {
                'RK'          => intval($value) ?: null,
                'TIERS'       => intval($value) ?: null,
                'AVG'         => floatval($value) ?: null,
                'ADV'         => floatval($value) ?: null,
                'ECR vs ADP'  => floatval($value) ?: null,
                default       => $value,
            };
        }

        return array_filter([
            'fp_ranking'    => Arr::get($cleanData, 'RK'),
            'fp_tier'       => Arr::get($cleanData, 'TIERS'),
            'fp_adp'        => Arr::get($cleanData, 'AVG'),
            'fp_adv'        => Arr::get($cleanData, 'ADV'),
            'fp_ecr_vs_adp' => Arr::get($cleanData, 'ECR vs ADP'),
        ]);
    }

    public function tearDown()
    {
        fclose($this->fp);
    }
}
