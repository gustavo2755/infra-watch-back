<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Server;
use App\Repositories\MonitoringQueueRepository;
use SplQueue;

/**
 * Operates the in-memory monitoring queue with SplQueue.
 * Enqueues eligible servers, processes jobs one at a time, and respects a 30-second cooldown window.
 */
final class QueueService
{
    private SplQueue $queue;

    /**
     * @var array<int, true>
     */
    private array $queuedServerIds = [];

    /**
     * @var array<int, true>
     */
    private array $runningServerIds = [];

    public function __construct(
        private MonitoringQueueRepository $monitoringQueueRepository,
        private MonitoringService $monitoringService,
        private int $cooldownSeconds = 30
    ) {
        $this->queue = new SplQueue();
    }

    public function enqueueEligibleServers(): int
    {
        $added = 0;
        $servers = $this->monitoringQueueRepository->listEligibleServers($this->cooldownSeconds);

        foreach ($servers as $server) {
            $serverId = $server->getId();

            if ($serverId === null) {
                continue;
            }

            if (isset($this->runningServerIds[$serverId]) || isset($this->queuedServerIds[$serverId])) {
                continue;
            }

            $this->queue->enqueue($server);
            $this->queuedServerIds[$serverId] = true;
            $added++;
        }

        return $added;
    }

    public function processNext(): bool
    {
        if ($this->queue->isEmpty()) {
            return false;
        }

        $server = $this->queue->dequeue();
        $serverId = $server->getId();

        if ($serverId === null) {
            return false;
        }

        unset($this->queuedServerIds[$serverId]);

        if (isset($this->runningServerIds[$serverId])) {
            return false;
        }

        $this->runningServerIds[$serverId] = true;

        try {
            $this->monitoringService->processServer($server);
        } finally {
            unset($this->runningServerIds[$serverId]);
        }

        return true;
    }

    public function runCycle(): int
    {
        $this->enqueueEligibleServers();

        $processed = 0;
        while ($this->processNext()) {
            $processed++;
        }

        return $processed;
    }

    public function getQueueSize(): int
    {
        return $this->queue->count();
    }
}
