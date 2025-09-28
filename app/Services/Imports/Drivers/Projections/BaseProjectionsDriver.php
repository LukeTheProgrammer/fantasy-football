<?php

namespace App\Services\Imports\Drivers\Projections;

use Illuminate\Support\Collection;

abstract class BaseProjectionsDriver
{
    public ?Collection $config = null;

    public array $dataMap = [];

    public array $dataProps = [
        'player_name',
        'team',
        'points',
        'rank',
    ];

    public array $errors = [];

    public array $fileProps = [];

    public function import(array $config = [])
    {
        $this->setUp($config);

        $this->load();

        $this->tearDown();
    }

    abstract public function setUp(array $config = []);

    abstract public function load();

    abstract public function tearDown();
}
