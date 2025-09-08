<?php

namespace App\Services\Actions;

use App\Services\Actions\Models\ModelActions;

class ActionService
{
    public function model(string $modelClassName)
    {
        return new ModelActions($modelClassName);
    }
}
