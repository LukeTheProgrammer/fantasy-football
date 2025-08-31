<?php

namespace App\Services\Imports\Drivers\Rankings;

use App\Enums\RankingSourcesEnum;
use App\Models\DraftRanking;
use App\Models\Player;
use App\Services\Imports\Drivers\BaseImportDriver;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FantasyProsDriver extends BaseImportDriver
{
    public $fp;

    public array $headers = [];

    public array $dataMap = [
        'RK' => 'rank',
        'TIERS' => 'tier',
        'AVG' => 'adp',
        'ADV' => 'adv',
        'AVG.' => 'adp',
        'ADV.' => 'adv',
    ];

    public ?int $year = null;

    public ?Carbon $rankedAt = null;

    public ?string $type = null;

    public ?string $source = null;

    public ?float $ppr = null;

    public function __construct(
        public string $filePath,
        public string $fileType = 'csv',
    ) {
        $this->source = RankingSourcesEnum::FANTASY_PROS->value;
    }

    public function import()
    {
        $this->setUp();

        $this->loadFile();

        $this->tearDown();
    }

    public function setUp(array $options = [])
    {
        if (!file_exists($this->filePath)) {
            throw new Exception('File does not exist ' . $this->filePath);
        }

        $this->year = Arr::get($options, 'year', date('Y'));
        $this->rankedAt = Arr::get($options, 'ranked_at', Carbon::now());
        $this->type = Arr::get($options, 'type', 'redraft');
        $this->ppr = Arr::get($options, 'ppr', 0);
    }

    public function loadFile()
    {
        $this->fp = fopen($this->filePath, 'r');

        $this->headers = fgetcsv($this->fp);
    }

    public function getNextLine()
    {
        if (($line = fgetcsv($this->fp)) !== false) {
            $data = array_combine($this->headers, $line);
            return $data;
        }

        return false;
    }

    public function saveRanking(Player $player, array $data)
    {
        $data = $this->prepareRankingData($data);

        $find = [
            'player_id' => $player->id,
            'year'      => $this->year,
            'ranked_at' => $this->rankedAt->toDateString(),
            'type'      => $this->type,
            'source'    => $this->source,
            'ppr'       => $this->ppr,
        ];

        $update = array_filter([
            'rank'      => Arr::get($data, 'rank'),
            'tier'      => Arr::get($data, 'tier'),
            'adp'       => Arr::get($data, 'adp'),
            'adv'       => Arr::get($data, 'adv'),
        ]);

        DraftRanking::updateOrCreate($find, $update);
    }

    public function prepareRankingData(array $item)
    {
        $ranking = [];

        foreach ($this->fieldsToImport as $field) {
            $dbField = $this->dataMap[$field];

            $ranking[$dbField] = $this->formatValue(
                Arr::get($item, $field),
                $field,
            );
        }

        return array_filter($ranking);
    }

    public function formatValue($rawValue, $field)
    {
        $value = preg_replace('/[^0-9.-]/', '', $rawValue);

        return match ($field) {
            'RK'          => intval($value) ?: null,
            'TIERS'       => intval($value) ?: null,
            'AVG'         => floatval($value) ?: null,
            'ADV'         => floatval($value) ?: null,
            default       => $value,
        };
    }

    public function tearDown()
    {
        fclose($this->fp);
    }
}
