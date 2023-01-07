<?php

namespace AnanasWeb\LaravelMonitoring\Tests\Stubs;

use AnanasWeb\LaravelMonitoring\Collectors\CollectorInterface;
use AnanasWeb\LaravelMonitoring\MetricsExporter;
use Prometheus\Gauge;

class FakeCollector implements CollectorInterface
{
    private Gauge $gauge;

    public function getName(): string
    {
        return 'fake_gauge';
    }

    public function registerMetrics(MetricsExporter $exporter): void
    {
        $this->gauge = $exporter->getOrRegisterGauge(
            'fake_gauge_value',
            'Test gauge',
            ['group']
        );
    }

    public function collect(): void
    {
        $this->gauge->set(1, ['group1']);
        $this->gauge->set(2, ['group2']);
    }
}
