<?php

namespace AnanasWeb\LaravelMonitoring\Middleware;

use AnanasWeb\LaravelMonitoring\MetricsExporter;
use AnanasWeb\LaravelMonitoring\Support\CPUTiming;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Histogram;
use Psr\Log\LoggerInterface;

class RequestTimingMiddleware
{
    use CPUTiming;

    private LoggerInterface $logger;

    private Histogram $requestHistogram;

    /**
     * @throws MetricsRegistrationException
     * @throws BindingResolutionException
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        /** @var MetricsExporter $monitoring */
        $monitoring = Container::getInstance()->make(MetricsExporter::class);
        $this->requestHistogram = $monitoring->getOrRegisterHistogram(
            'application_request_execution_time',
            'Timing of execution jobs',
            [
                'hostname',
                'method',
                'uri',
                'code',
            ],
            config('monitoring.request_buckets') ?? null
        );
    }

    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startCpuUsage = getrusage();
        $startTime = microtime(true);
        /** @var Response $response */
        $response = $next($request);
        $endTime = microtime(true);
        $endCpuUsage = getrusage();

        $this->logger->info('REQUEST_PROCESSED', [
            'routeName' => $request->route()->getName(),
            'routeUrl' => $request->route()->uri(),
            'controllerClass' => $request->route()->controller ? $request->route()->getControllerClass() : null,
            'controllerActionMethod' => $request->route()->controller ? $request->route()->getActionMethod() : null,
            'parameters' => json_encode($request->route()->parameters()),
            'requestPayload' => json_encode($request->all()),
            'requestPayloadFiles' => json_encode($request->allFiles()),
            'requestProcessingTime' => $endTime - $startTime,
            'requestStartedAt' => $startTime,
            'requestFinishedAt' => $endTime,
            'requestMemoryPeakUsage' => memory_get_peak_usage(true),
            'requestUserCpuTime' => $this->cpuTime($endCpuUsage, $startCpuUsage, 'utime'),
            'requestSystemCpuTime' => $this->cpuTime($endCpuUsage, $startCpuUsage, 'stime'),
        ]);

        $this->requestHistogram->observe(
            $endTime - $startTime,
            [
                gethostname() ?? '',
                $request->getMethod(),
                $request->route()->uri(),
                $response->getStatusCode(),
            ]
        );

        return $response;
    }
}
