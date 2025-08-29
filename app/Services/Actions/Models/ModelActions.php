<?php

namespace App\Services\Actions\Models;

use App\Actions\Models\Draft\DraftCreateAction;
use App\Actions\Models\LeagueMembers\LeagueMemberCreateAction;
use App\Actions\Models\LeagueMembers\LeagueMemberUpdateAction;
use App\Actions\Models\LeagueSettings\LeagueSettingsCreateAction;
use App\Actions\Models\LeagueSettings\LeagueSettingsUpdateAction;
use App\Actions\Models\Leagues\LeagueCreateAction;
use App\Actions\Models\Leagues\LeagueUpdateAction;
use App\Actions\Models\Player\PlayerCreateAction;
use App\Actions\Models\Player\PlayerUpsertAction;
use App\Models\Draft;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use App\Models\Player;
use Exception;
use Illuminate\Support\Arr;

class ModelActions
{
    public $registry = [
        Draft::class => [
            'create' => DraftCreateAction::class,
        ],
        League::class => [
            'create' => LeagueCreateAction::class,
            'update' => LeagueUpdateAction::class,
        ],
        LeagueSettings::class => [
            'create' => LeagueSettingsCreateAction::class,
            'update' => LeagueSettingsUpdateAction::class,
        ],
        LeagueMember::class => [
            'create' => LeagueMemberCreateAction::class,
            'update' => LeagueMemberUpdateAction::class,
        ],
        Player::class => [
            'create' => PlayerCreateAction::class,
            'upsert' => PlayerUpsertAction::class,
        ],
    ];

    public function __construct(public string $modelClassName)
    {
        //
    }

    public function create(...$args)
    {
        $actions = Arr::get($this->registry, $this->modelClassName);
        $action = Arr::get($actions, 'create', false);

        if (! $action) {
            throw new Exception('There is no create method registered for ' . $this->modelClassName);
        }

        return app($action)->run(...$args);
    }

    public function update(...$args)
    {
        $actions = Arr::get($this->registry, $this->modelClassName);
        $action = Arr::get($actions, 'update', false);

        if (! $action) {
            throw new Exception('There is no update method registered for ' . $this->modelClassName);
        }

        return app($action)->run(...$args);
    }

    public function upsert(...$args)
    {
        $actions = Arr::get($this->registry, $this->modelClassName);
        $action = Arr::get($actions, 'upsert', false);

        if (! $action) {
            throw new Exception('There is no upsert method registered for ' . $this->modelClassName);
        }

        return app($action)->run(...$args);
    }
}
