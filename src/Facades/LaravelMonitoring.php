<?php

namespace AnanasWeb\LaravelMonitoring\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AnanasWeb\LaravelMonitoring\LaravelMonitoring
 */
class LaravelMonitoring extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \AnanasWeb\LaravelMonitoring\LaravelMonitoring::class;
    }
}
