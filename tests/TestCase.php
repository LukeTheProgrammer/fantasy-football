<?php

namespace Tests;

use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Positions and teams are fixed lists rather than test data, so every test
     * starts with the same reference rows the app runs on.
     */
    protected bool $seed = true;

    /**
     * @var string
     */
    protected $seeder = ReferenceDataSeeder::class;
}
