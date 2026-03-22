<?php

declare(strict_types=1);

namespace Tests\Commands;

use App\Commands\RunMonitoringQueueCommand;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Repositories\MonitoringQueueRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Services\MonitoringService;
use App\Services\QueueService;
use Tests\DatabaseTestCase;

final class RunMonitoringQueueCommandTest extends DatabaseTestCase
{
    public function testExecuteProcessesEligibleServers(): void
    {
        $serverId = $this->createServer('queue-server-a', '10.0.10.1');
        $queueRepository = new MonitoringQueueRepository($this->pdo);
        $monitoringService = new MonitoringService(
            new MonitoringLogRepository($this->pdo),
            new MonitoringLogServiceCheckRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo),
            $queueRepository
        );
        $queueService = new QueueService($queueRepository, $monitoringService, 30);
        $command = new RunMonitoringQueueCommand($queueService, 0);

        $total = $command->execute(1);

        $this->assertSame(1, $total);
        $count = $this->pdo->query("SELECT COUNT(*) FROM monitoring_logs WHERE server_id = $serverId")->fetchColumn();
        $this->assertSame(1, (int) $count);
    }

    public function testExecuteRespectsCooldownAcrossCycles(): void
    {
        $this->createServer('queue-server-b', '10.0.10.2');
        $queueRepository = new MonitoringQueueRepository($this->pdo);
        $monitoringService = new MonitoringService(
            new MonitoringLogRepository($this->pdo),
            new MonitoringLogServiceCheckRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo),
            $queueRepository
        );
        $queueService = new QueueService($queueRepository, $monitoringService, 30);
        $command = new RunMonitoringQueueCommand($queueService, 0);

        $total = $command->execute(2);

        $this->assertSame(1, $total);
    }

    private function createServer(string $name, string $ipAddress): int
    {
        $this->pdo->exec(
            "INSERT INTO servers (name, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, last_check_at, created_at, updated_at) VALUES ('$name', '$ipAddress', 1, 1, 8, 16, 100, 60, 30, 90, 90, 90, 90, 1, 1, 1, 1, NULL, datetime('now'), datetime('now'))"
        );

        return (int) $this->pdo->lastInsertId();
    }
}
