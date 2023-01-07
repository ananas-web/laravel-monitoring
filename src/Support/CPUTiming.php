<?php

namespace AnanasWeb\LaravelMonitoring\Support;

trait CPUTiming
{
    /**
     * Calculate difference in between two points in time base of response of getrusage() function
     *
     * @param array $end
     * @param array $begin
     * @param string $indexName
     * @return float
     */
    protected function cpuTime(array $end, array $begin, string $indexName): float
    {
        return (
                $end["ru_$indexName.tv_sec"] - $begin["ru_$indexName.tv_sec"]
            ) + (
                $end["ru_$indexName.tv_usec"] - $begin["ru_$indexName.tv_usec"]
            ) / 1000000;
    }
}
