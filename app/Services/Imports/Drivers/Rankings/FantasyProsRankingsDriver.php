<?php

namespace App\Services\Imports\Drivers\Rankings;

use App\Enums\DataSources;
use App\Models\DraftRanking;
use App\Models\Player;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FantasyProsRankingsDriver extends BaseRankingsDriver
{
    // File pointer
    public $fp;

    public ?int $year = null;

    public ?Carbon $rankedAt = null;

    public ?string $type = null;

    public ?string $source = null;

    public ?float $ppr = null;

    public function __construct(
        public string $filePath,
        public string $fileType = 'csv',
    ) {
        $this->source = DataSources::FANTASY_PROS->value;
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
        if (empty($this->dataMap)) {
            throw new Exception('Data map must be set with at least one prop.');
        }

        $ranking = [];

        foreach ($this->dataMap as $dbProp => $fileKey) {
            $ranking[$dbProp] = $this->formatValue(
                Arr::get($item, $fileKey),
                $dbProp,
            );
        }

        return array_filter($ranking);
    }

    public function formatValue($rawValue, $dbProp)
    {
        $ints = ['rank', 'tier'];
        $floats = ['adp', 'adv'];

        if (! in_array($dbProp, $ints) && ! in_array($dbProp, $floats)) {
            return $rawValue;
        }

        $value = preg_replace('/[^0-9.-]/', '', $rawValue);

        return (in_array($dbProp, $ints))
            ? intval($value)
            : floatval($value);
    }

    public function tearDown()
    {
        fclose($this->fp);
    }
}
