<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\EspnService;
use InvalidArgumentException;

abstract class BaseResourceCollection
{
    protected ?string $returnType = 'raw';

    protected bool $forcePull = false;

    public function return(string $type)
    {
        if (! in_array($type, EspnService::RETURN_TYPES)) {
            throw new InvalidArgumentException('Invalid return type: ' . $type);
        }

        $this->returnType = $type;

        return $this;
    }

    public function forcePull(bool $forcePull = false)
    {
        $this->forcePull = $forcePull;

        return $this;
    }
}
