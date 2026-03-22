<?php

declare(strict_types=1);

namespace App\Monitoring;

/**
 * Generates fake service check results for mocked monitoring.
 */
final class MockServiceCheckGenerator
{
    /**
     * @return array{is_running: bool, output_message: string}
     */
    public function generate(): array
    {
        $isRunning = random_int(0, 1) === 1;

        return [
            'is_running' => $isRunning,
            'output_message' => $isRunning ? 'Service is running' : 'Service is not running',
        ];
    }
}
