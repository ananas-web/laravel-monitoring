<?php

namespace AnanasWeb\LaravelMonitoring\Controllers;

use AnanasWeb\LaravelMonitoring\MetricsExporter;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ResponseFactory;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    /**
     * GET /metrics
     *
     * The route path is configurable in the monitoring.metrics.path config var
     *
     * @param  ResponseFactory  $responseFactory
     * @param  MetricsExporter  $metricsExporter
     * @return Response
     */
    public function __invoke(ResponseFactory $responseFactory, MetricsExporter $metricsExporter): Response
    {
        $metrics = $metricsExporter->export();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($metrics);

        return $responseFactory->make($result, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
    }
}
