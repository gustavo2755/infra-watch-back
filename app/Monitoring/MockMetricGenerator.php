<?php

declare(strict_types=1);

namespace App\Monitoring;

/**
 * Generates fake resource metrics for mocked monitoring.
 * Produces values in the 0.00–50.00% range to avoid unintentional threshold triggers.
 */
final class MockMetricGenerator
{
    /**
     * @return array{cpu_usage_percent: float, ram_usage_percent: float, disk_usage_percent: float, bandwidth_usage_percent: float}
     */
    public function generate(): array
    {
        return [
            'cpu_usage_percent' => $this->randomMetric(),
            'ram_usage_percent' => $this->randomMetric(),
            'disk_usage_percent' => $this->randomMetric(),
            'bandwidth_usage_percent' => $this->randomMetric(),
        ];
    }

    public function randomMetric(): float
    {
        return random_int(0, 5000) / 100;
    }
}
