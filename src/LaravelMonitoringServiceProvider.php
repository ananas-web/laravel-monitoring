<?php

namespace AnanasWeb\LaravelMonitoring;

use AnanasWeb\LaravelMonitoring\Commands\LaravelMonitoringCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMonitoringServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-monitoring')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-monitoring_table')
            ->hasCommand(LaravelMonitoringCommand::class);
    }
}
