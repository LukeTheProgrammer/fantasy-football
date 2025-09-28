<?php

namespace App\Providers;

use App\Services\Actions\ActionService;
use App\Services\Espn\EspnService;
use App\Services\FantasyPros\FantasyProsService;
use App\Services\Imports\ImportService;
use App\Services\ProFootballReference\ProFootballReferenceService;
use App\Services\Scrapers\ScraperService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        'Action'               => ActionService::class,
        'Espn'                 => EspnService::class,
        'FantasyPros'          => FantasyProsService::class,
        'Import'               => ImportService::class,
        'ProFootballReference' => ProFootballReferenceService::class,
        'Scraper'              => ScraperService::class,
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
        JsonResource::withoutWrapping();
    }
}
