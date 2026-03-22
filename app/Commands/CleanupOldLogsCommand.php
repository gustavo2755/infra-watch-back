<?php

declare(strict_types=1);

namespace App\Commands;

use App\Repositories\MonitoringLogRepository;
use App\Repositories\ServerRepository;
use DateTimeImmutable;

/**
 * Cleans up old monitoring logs based on each server retention_days setting.
 */
final class CleanupOldLogsCommand
{
    public function __construct(
        private readonly ServerRepository $serverRepository,
        private readonly MonitoringLogRepository $monitoringLogRepository,
        private readonly int $defaultRetentionDays = 30
    ) {
    }

    public function execute(): int
    {
        $deletedTotal = 0;
        $servers = $this->serverRepository->list();

        foreach ($servers as $server) {
            $serverId = $server->getId();

            if ($serverId === null) {
                continue;
            }

            $retentionDays = $server->getRetentionDays() ?? $this->defaultRetentionDays;
            $cutoff = (new DateTimeImmutable("-{$retentionDays} days"))->format('Y-m-d H:i:s');
            $deletedTotal += $this->monitoringLogRepository->deleteOlderThanByServer($serverId, $cutoff);
        }

        return $deletedTotal;
    }
}
