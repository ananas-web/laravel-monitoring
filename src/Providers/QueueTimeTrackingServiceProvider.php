<?php /** @noinspection PhpUndefinedFieldInspection */

namespace AnanasWeb\LaravelMonitoring\Providers;

use AnanasWeb\LaravelMonitoring\MetricsExporter;
use AnanasWeb\LaravelMonitoring\Support\CPUTiming;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\ServiceProvider;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Histogram;
use Psr\Log\LoggerInterface;

class QueueTimeTrackingServiceProvider extends ServiceProvider
{
    use CPUTiming, InteractsWithTime;

    private Histogram $queueHistogram;

    /**
     * @throws MetricsRegistrationException
     */
    public function register()
    {
        $this->app['queue']->before([$this, 'setUpJobLogging']);
        $this->app['queue']->after([$this, 'finishJobProcessing']);

        /** @var MetricsExporter $monitoring */
        $monitoring = $this->app['monitoring'];
        $this->queueHistogram = $monitoring->getOrRegisterHistogram(
            'application_queue_jobs_execution_time',
            'Timing of execution jobs',
            [
                'queue',
                'hostname',
                'jobName',
                'succeed',
            ],
            config('monitoring.queue_buckets')
        );
    }

    protected function setUpJobLogging(JobProcessing $event)
    {
        if (!config('monitoring.track_queue_performance', true)) {
            $event->job->measurable = false;
            return;
        }

        if ($event->job->disableMeasure ?? false) {
            $event->job->measurable = false;
            return;
        }

        $payload = $event->job->payload();

        $event->job->measurable = true;
        $event->job->pushedAt = $payload['pushedAt'] ?? null;
        $event->job->startedAt = microtime(true);
        $event->job->startCpuUsage = getrusage();
    }

    protected function finishJobProcessing(JobProcessed $event)
    {
        if (!$event->job->measurable) {
            return;
        }

        $finishedAt = microtime(true);
        $cpuUsage = getrusage();

        app(LoggerInterface::class)->info('JOB_PROCESSED', [
            'hostname' => gethostname() ?? '',
            'jobName' => $event->job->resolveName(),
            'jobId' => $event->job->getJobId(),
            'jobProcessingTime' => $finishedAt - $event->job->startedAt,
            'jobAttemptCount' => $event->job->attempts(),
            'jobPushedAt' => $event->job->pushedAt,
            'jobStartedAt' => $event->job->startedAt,
            'jobQueue' => $event->job->getQueue(),
            'jobMaxTries' => $event->job->maxTries(),
            'jobIsFailed' => $event->job->hasFailed(),
            'jobFinishedAt' => $finishedAt,
            'jobMemoryPeakUsage' => memory_get_peak_usage(true),
            'jobUserCpuTime' => $this->cpuTime($cpuUsage, $event->job->startCpuUsage, 'utime'),
            'jobSystemCpuTime' => $this->cpuTime($cpuUsage, $event->job->startCpuUsage, 'stime'),
        ]);

        $this->queueHistogram->observe(
            $finishedAt - $event->job->startedAt,
            [
                $event->job->getQueue(),
                gethostname() ?? '',
                $event->job->resolveName(),
                $event->job->hasFailed(),
            ]
        );
    }
}
