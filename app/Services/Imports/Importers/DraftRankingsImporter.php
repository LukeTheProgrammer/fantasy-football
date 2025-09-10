<?php

namespace App\Services\Imports\Importers;

use App\Models\Player;
use App\Services\Imports\Drivers\Rankings\BaseRankingsDriver;

class DraftRankingsImporter
{
    public function __construct(public ?BaseRankingsDriver $driver = null)
    {
        //
    }

    public function getHeaders()
    {
        return $this->driver->headers;
    }

    public function import()
    {
        return $this->driver->import();
    }

    public function setUp()
    {
        return $this->driver->setUp();
    }

    public function loadFile()
    {
        return $this->driver->loadFile();
    }

    public function getNextLine()
    {
        return $this->driver->getNextLine();
    }

    public function tearDown()
    {
        return $this->driver->tearDown();
    }

    public function saveRanking(Player $player, array $data)
    {
        $this->driver->saveRanking($player, $data);
    }
}
