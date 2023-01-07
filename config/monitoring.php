<?php

// config for AnanasWeb/LaravelMonitoring
return [
    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace to use as a prefix for all metrics.
    |
    | This will typically be the name of your project, eg: 'search'.
    |
    */

    'namespace' => env('PROMETHEUS_NAMESPACE', 'UNSET'),

    'track_queue_performance' => env('LARAVEL_MONITORING_QUEUE', false),

    'metrics' => [
        'enabled' => env('LARAVEL_MONITORING_METRICS_ENABLED', true),
        'path' => '/metrics',
        'prefix' => '',
        'middleware' => [],
    ],

    'storage_adapter' => env('LARAVEL_MONITORING_METRICS_STORAGE_ADAPTER', 'memory'),

    'storage_adapters' => [
        'redis' => [
            'host' => env('LARAVEL_MONITORING_METRICS_REDIS_HOST', 'localhost'),
            'port' => env('LARAVEL_MONITORING_METRICS_REDIS_PORT', 6379),
            'database' => env('LARAVEL_MONITORING_METRICS_REDIS_DATABASE', 0),
            'timeout' => env('LARAVEL_MONITORING_METRICS_REDIS_TIMEOUT', 0.1),
            'read_timeout' => env('LARAVEL_MONITORING_METRICS_REDIS_READ_TIMEOUT', 10),
            'persistent_connections' => env('LARAVEL_MONITORING_METRICS_REDIS_PERSISTENT_CONNECTIONS', false),
            'prefix' => env('LARAVEL_MONITORING_METRICS_REDIS_PREFIX', 'LARAVEL_MONITORING_METRICS_'),
            'prefix_dynamic' => env('LARAVEL_MONITORING_METRICS_REDIS_PREFIX_DYNAMIC', true),
        ],
    ],

    'collectors' => [],

    'queue_buckets' => null,
    'request_buckets' => null,
];
