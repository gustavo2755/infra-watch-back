<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\QueueService;

/**
 * Runs monitoring queue cycles continuously or for a limited amount of cycles.
 */
final class RunMonitoringQueueCommand
{
    public function __construct(
        private readonly QueueService $queueService,
        private readonly int $intervalSeconds = 30
    ) {
    }

    public function execute(?int $maxCycles = null): int
    {
        $totalProcessed = 0;
        $cycle = 0;

        while (true) {
            $totalProcessed += $this->queueService->runCycle();
            $cycle++;

            if ($maxCycles !== null && $cycle >= $maxCycles) {
                break;
            }

            sleep($this->intervalSeconds);
        }

        return $totalProcessed;
    }
}
