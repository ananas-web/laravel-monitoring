<?php

namespace AnanasWeb\LaravelMonitoring\Tests\Support;

use AnanasWeb\LaravelMonitoring\LaravelMonitoringServiceProvider;
use AnanasWeb\LaravelMonitoring\Support\Monitoring;
use AnanasWeb\LaravelMonitoring\Tests\TestCase;
use Prometheus\Exception\MetricNotFoundException;
use Throwable;

class MonitoringTest extends TestCase
{
    private Monitoring $monitoringInstance;

    public function setUp(): void
    {
        parent::setUp();

        $this->monitoringInstance = $this->app->make(Monitoring::class);
    }

    public function testErrorIfCounterInNotInitialised()
    {
        try {
            $this->monitoringInstance->counter('missing_counter');
            $this->fail('Exception should be thrown, because counter should not be defined');
        } catch (MetricNotFoundException $e) {
            $this->addToAssertionCount(1);
        } catch (Throwable $e) {
            $this->fail('Unexpected exception thrown: '.$e->getMessage());
        }
    }

    public function testReceiveCounter()
    {
        $this->monitoringInstance->registerCounter('testing_counter', 'Counter for testing proposes', ['l1', 'l2']);
        $this->monitoringInstance->counter('testing_counter');
        $this->addToAssertionCount(1);
    }

    public function testIncreaseCounter()
    {
        $this->monitoringInstance->registerCounter('testing_counter', 'Counter for testing proposes', ['l1', 'l2']);

        $this->monitoringInstance->incrementCounter('testing_counter', [200, 'POST']);
        $this->addToAssertionCount(1);
    }

    public function testIncreaseCounterForFive()
    {
        $this->monitoringInstance->registerCounter('testing_counter', 'Counter for testing proposes', ['l1', 'l2']);
        $this->monitoringInstance->incrementCounter('testing_counter', [200, 'POST']);
        $this->monitoringInstance->counter('testing_counter');
        $this->addToAssertionCount(1);
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelMonitoringServiceProvider::class];
    }

    public function getEnvironmentSetUp($app)
    {
//        config()
    }
}
