<?php

namespace App\Providers;

use App\Services\Actions\ActionService;
use App\Services\Espn\EspnService;
use App\Services\Imports\ImportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        'Action' => ActionService::class,
        'Espn' => EspnService::class,
        'Import' => ImportService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
