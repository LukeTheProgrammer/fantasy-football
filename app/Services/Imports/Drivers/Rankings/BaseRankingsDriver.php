<?php

namespace App\Services\Imports\Drivers\Rankings;

use App\Models\Player;
use App\Services\Imports\Contracts\ImportDriver;

abstract class BaseRankingsDriver implements ImportDriver
{
    public array $dataMap = [];

    public array $headers = [];

    abstract public function import();

    abstract public function setUp(array $options = []);

    abstract public function loadFile();

    abstract public function getNextLine();

    abstract public function tearDown();

    abstract public function saveRanking(Player $player, array $data);
}
