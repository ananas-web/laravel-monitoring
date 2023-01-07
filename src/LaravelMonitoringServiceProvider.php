<?php

namespace AnanasWeb\LaravelMonitoring;

use AnanasWeb\LaravelMonitoring\Storage\StorageAdapterFactory;
use Illuminate\Support\Arr;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMonitoringServiceProvider extends PackageServiceProvider
{
    /**
     * @throws InvalidPackage
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(MetricsExporter::class, function ($app) {
            $adapter = $app['monitoring.storage_adapter'];
            $prometheus = new CollectorRegistry($adapter, true);
            $exporter = new MetricsExporter(config('monitoring.namespace'), $prometheus);
            foreach (config('monitoring.collectors') as $collectorClass) {
                $collector = $this->app->make($collectorClass);
                $exporter->registerCollector($collector);
            }
            return $exporter;
        });
        $this->app->alias(MetricsExporter::class, 'prometheus');
        $this->app->alias(MetricsExporter::class, 'monitoring');

        $this->app->bind('monitoring.storage_adapter_factory', function () {
            return new StorageAdapterFactory();
        });
        $this->app->alias('monitoring.storage_adapter_factory', 'prometheus.storage_adapter_factory');


        $this->app->bind(Adapter::class, function ($app) {
            /* @var StorageAdapterFactory $factory */
            $factory = $app['monitoring.storage_adapter_factory'];
            $driver = config('monitoring.storage_adapter');
            $configs = config('monitoring.storage_adapters');
            $config = Arr::get($configs, $driver, []);

            return $factory->make($driver, $config);
        });
        $this->app->alias(Adapter::class, 'prometheus.storage_adapter');
        $this->app->alias(Adapter::class, 'monitoring.storage_adapter');
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-monitoring')
            ->hasConfigFile()
            ->hasRoute('monitoring');
    }


}
