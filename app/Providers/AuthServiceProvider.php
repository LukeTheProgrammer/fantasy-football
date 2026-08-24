<?php

namespace App\Providers;

use App\Models\Draft;
use App\Models\League;
use App\Policies\DraftPolicy;
use App\Policies\LeaguePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Draft::class  => DraftPolicy::class,
        League::class => LeaguePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
