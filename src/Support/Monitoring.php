<?php

namespace AnanasWeb\LaravelMonitoring\Support;

use AnanasWeb\LaravelMonitoring\Exceptions\InvalidArgumentException;
use AnanasWeb\LaravelMonitoring\MetricsExporter;
use Prometheus\Counter;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use Prometheus\Histogram;

class Monitoring
{
    private MetricsExporter $exporter;

    public function __construct(MetricsExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     *
     * @throws MetricsRegistrationException
     */
    public function registerGauge(string $name, string $help, $labels) {
        return $this->exporter->getOrRegisterGauge($name, $help, $labels);
    }

    /**
     * Register counter.
     *
     * @throws MetricsRegistrationException
     */
    public function registerCounter(string $name, string $help, $labels) {
        return $this->exporter->getOrRegisterCounter($name, $help, $labels);
    }

    /**
     * Get gauge instance
     *
     * @param string $name Name of gauge
     * @return Gauge
     * @throws MetricNotFoundException If gauge is not registered
     */
    public function gauge(string $name): Gauge
    {
        return $this->exporter->getGauge($name);
    }

    /**
     * Get counter instance
     *
     * @param string $name Name of counter
     * @return Counter
     * @throws MetricNotFoundException If counter is not registered
     */
    public function counter(string $name): Counter
    {
        return $this->exporter->getCounter($name);
    }

    /**
     * Get histogram instance
     *
     * @param string $name Name of histogram
     * @return Histogram
     * @throws MetricNotFoundException If histogram is not registered
     */
    public function histogram(string $name): Histogram
    {
        return $this->exporter->getHistogram($name);
    }

    /**
     * Increments counter values. Counter with provided name should be registered.
     *
     * @param string $name Name of counter
     * @param array $labels Labels values associated with counter (default empty array)
     * @param int|float $count Value for incrementing (default 1)
     * @return void
     * @throws MetricNotFoundException If counter is not registred
     * @throws InvalidArgumentException If $count is negative
     */
    public function incrementCounter(string $name, array $labels = [], int|float $count = 1): void
    {
        if ($count < 0) {
            throw new InvalidArgumentException("Provided value $count for counter $name is not valid. It should be positive number");
        }

        $this->counter($name)->incBy($count, $labels);
    }

    /**
     * Increments values of gauge
     *
     * @param string $name Name of gauge
     * @param array $labels Labels values associated with gauge (default empty array)
     * @param float|int $count Value for incrementing (default 1)
     * @return void
     * @throws MetricNotFoundException If metric is not registered
     */
    public function incrementGauge(string $name, array $labels = [], float|int $count = 1): void
    {
        $this->gauge($name)->incBy($count, $labels);
    }

    /**
     * Decrements values of gauge
     *
     * @param string $name Name of gauge
     * @param array $labels Labels values associated with gauge (default empty array)
     * @param float|int $count Value for decrementing (default 1)
     * @return void
     * @throws MetricNotFoundException If metric is not registered
     */
    public function decrementGauge(string $name, array $labels = [], float|int $count = 1): void
    {
        $this->gauge($name)->decBy($count, $labels);
    }

    /**
     * Save value to histogram
     *
     * @param string $name Name of gauge
     * @param float|int $value Value for observe
     * @param array $labels Labels values associated with histogram (default empty array)
     * @return void
     * @throws MetricNotFoundException If metric is not registered
     */
    public function observeHistogram(string $name, float|int $value, array $labels = []): void
    {
        $this->histogram($name)->observe($value, $labels);
    }

    /**
     * Execute callable and record measurement to histogram
     *
     * @param callable $callback Function to be executed
     * @param string $name Name of histogram
     * @param array $labels Labels values associated with histogram (default empty array)
     * @return mixed Value returned by $callback
     * @throws MetricNotFoundException If histogram is not registered
     */
    public function measureAndObserve(callable $callback, string $name, array $labels = []): mixed
    {
        $startTime = microtime(true);
        $response = $callback();
        $endTime = microtime(true);
        $this->observeHistogram($name, $endTime - $startTime, $labels);
        return $response;
    }
}
