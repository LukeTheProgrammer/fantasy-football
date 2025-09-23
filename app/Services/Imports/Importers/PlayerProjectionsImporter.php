<?php

namespace App\Services\Imports\Importers;

use App\Models\LeagueMember;
use App\Models\Player;
use App\Services\Imports\Drivers\Projections\BaseProjectionsDriver;

class PlayerProjectionsImporter
{
    public function __construct(public ?BaseProjectionsDriver $driver = null)
    {
        //
    }

    public function dataProps()
    {
        return $this->driver->dataProps;
    }

    public function fileProps()
    {
        return $this->driver->fileProps;
    }

    public function getConfig(?string $key = null)
    {
        return ($key) ? $this->driver->config->get($key) : $this->driver->config->toArray();
    }

    public function getDataMap(?string $key = null)
    {
        return ($key) ? $this->driver->dataMap[$key] : $this->driver->dataMap;
    }

    public function setDataMap(array $dataMap)
    {
        $this->driver->dataMap = $dataMap;
    }

    public function getErrors()
    {
        return $this->driver->errors;
    }

    public function import()
    {
        return $this->driver->import();
    }

    public function setUp(array $config = [])
    {
        return $this->driver->setUp($config);
    }

    public function load()
    {
        return $this->driver->load();
    }

    public function tearDown()
    {
        return $this->driver->tearDown();
    }
}
