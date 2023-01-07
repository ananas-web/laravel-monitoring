<?php

namespace AnanasWeb\LaravelMonitoring\Tests;


use AnanasWeb\LaravelMonitoring\LaravelMonitoringServiceProvider;
use AnanasWeb\LaravelMonitoring\MetricsExporter;
use AnanasWeb\LaravelMonitoring\Storage\StorageAdapterFactory;
use AnanasWeb\LaravelMonitoring\Tests\Stubs\FakeCollector;
use Illuminate\Routing\Router;
use Prometheus\Storage\Adapter;

class LaravelMonitoringServiceProviderTest extends TestCase
{
    public function testServiceProvider(): void
    {
        $this->assertInstanceOf(Adapter::class, $this->app[Adapter::class]);
        $this->assertInstanceOf(MetricsExporter::class, $this->app[MetricsExporter::class]);
        $this->assertInstanceOf(StorageAdapterFactory::class, $this->app[StorageAdapterFactory::class]);

        $this->assertInstanceOf(Adapter::class, $this->app->get('prometheus.storage_adapter'));
        $this->assertInstanceOf(MetricsExporter::class, $this->app->get('prometheus'));
        $this->assertInstanceOf(StorageAdapterFactory::class, $this->app->get('prometheus.storage_adapter_factory'));

        $this->assertInstanceOf(Adapter::class, $this->app->get('monitoring.storage_adapter'));
        $this->assertInstanceOf(MetricsExporter::class, $this->app->get('monitoring'));
        $this->assertInstanceOf(StorageAdapterFactory::class, $this->app->get('monitoring.storage_adapter_factory'));

        /* @var \Illuminate\Support\Facades\Config $config */
        $config = $this->app['config'];
        $this->assertTrue($config->get('monitoring.metrics.enabled'));
        $this->assertEquals('/metrics', $config->get('monitoring.metrics.path'));
        $this->assertEmpty($config->get('monitoring.metrics.middleware'));
        $this->assertSame([FakeCollector::class], $config->get('monitoring.collectors'));
        $this->assertEquals('memory', $config->get('monitoring.storage_adapter'));

        /* @var Router $router */
        $router = $this->app['router'];
        $this->assertTrue($router->has('monitoring.metrics'));
    }

    public function testCollectors() {
        /** @var MetricsExporter $exporter */
        $exporter = $this->app[MetricsExporter::class];
        $exportData = $exporter->export();

        $this->assertCount(2, $exportData);
        $this->assertEquals('gauge', $exportData[0]->getType());
        $this->assertEquals('php_info', $exportData[0]->getName());
        $this->assertEquals('gauge', $exportData[1]->getType());
        $this->assertEquals('UNSET_fake_gauge_value', $exportData[1]->getName());
    }

    public function getEnvironmentSetUp($app)
    {
        config(['monitoring.collectors' => [
            FakeCollector::class,
        ]]);
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelMonitoringServiceProvider::class];
    }
}
