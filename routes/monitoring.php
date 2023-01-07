<?php

use AnanasWeb\LaravelMonitoring\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

if (config('monitoring.metrics.enabled')) {
    Route::get(
        config('monitoring.metrics.path')
    )->prefix(
        config('monitoring.metrics.prefix')
    )->middleware(
        config('monitoring.metrics.middleware')
    )->name('monitoring.metrics')
        ->uses(MetricsController::class);
}
