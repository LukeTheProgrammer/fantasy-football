<?php

namespace App\Traits;

use App\Enums\Datum;
use Exception;

trait HasDataFormats
{
    protected string $dataFormat = Datum::FORMAT_FORMATTED->value;

    protected bool $forcePull = false;

    public function dataFormat(string|Datum $format)
    {
        $format = ($format instanceof Datum) ? $format->value : $format;

        if (! in_array($format, Datum::FORMATS)) {
            throw new Exception('Invalid format: ' . $format);
        }

        $this->dataFormat = $format;

        return $this;
    }

    public function forcePull(bool $forcePull = false)
    {
        $this->forcePull = $forcePull;

        return $this;
    }
}
