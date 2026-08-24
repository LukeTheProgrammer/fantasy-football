<?php

namespace App\Providers;

use App\Services\Actions\ActionService;
use App\Services\CBS\CBSService;
use App\Services\Data\DataService;
use App\Services\Espn\EspnService;
use App\Services\FantasyPros\FantasyProsService;
use App\Services\Imports\ImportService;
use App\Services\Player\PlayerService;
use App\Services\ProFootballReference\ProFootballReferenceService;
use App\Services\Scrapers\ScraperService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        'Action'               => ActionService::class,
        'CBS'                  => CBSService::class,
        'Data'                 => DataService::class,
        'Espn'                 => EspnService::class,
        'FantasyPros'          => FantasyProsService::class,
        'Import'               => ImportService::class,
        'Player'               => PlayerService::class,
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
