<?php

namespace AnanasWeb\LaravelMonitoring\Collectors;

use AnanasWeb\LaravelMonitoring\MetricsExporter;

interface CollectorInterface
{
    /**
     * Return the name of the collector.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Register all metrics associated with the collector.
     *
     * The metrics needs to be registered on the exporter object.
     * eg:
     * ```php
     * $exporter->registerCounter('search_requests_total', 'The total number of search requests.');
     * ```
     *
     * @param  MetricsExporter  $exporter
     */
    public function registerMetrics(MetricsExporter $exporter): void;

    /**
     * Collect metrics data, if need be, before exporting.
     *
     * As an example, this may be used to perform time-consuming database queries and set the value of a counter
     * or gauge.
     */
    public function collect(): void;
}
