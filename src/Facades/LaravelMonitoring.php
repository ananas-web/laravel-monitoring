<?php

namespace AnanasWeb\LaravelMonitoring\Facades;

use AnanasWeb\LaravelMonitoring\MetricsExporter;
use Illuminate\Support\Facades\Facade;

/**
 * @see \AnanasWeb\LaravelMonitoring\LaravelMonitoring
 */
class LaravelMonitoring extends Facade
{
    /** @noinspection PhpMissingReturnTypeInspection */
    protected static function getFacadeAccessor()
    {
        return MetricsExporter::class;
    }
}
