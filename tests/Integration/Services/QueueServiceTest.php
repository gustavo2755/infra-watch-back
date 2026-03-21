<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Repositories\MonitoringQueueRepository;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Services\MonitoringService;
use App\Services\QueueService;
use Tests\DatabaseTestCase;

final class QueueServiceTest extends DatabaseTestCase
{
    private QueueService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $monitoringQueueRepository = new MonitoringQueueRepository($this->pdo);
        $monitoringService = new MonitoringService(
            new MonitoringLogRepository($this->pdo),
            new MonitoringLogServiceCheckRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo),
            $monitoringQueueRepository
        );

        $this->service = new QueueService($monitoringQueueRepository, $monitoringService, 30);
    }

    public function testEnqueueEligibleServers(): void
    {
        $eligibleA = $this->createServer('srv-a', '10.0.0.31', 1, null);
        $eligibleB = $this->createServer('srv-b', '10.0.0.32', 1, "datetime('now', '-40 seconds')");
        $this->createServer('srv-c', '10.0.0.33', 0, null);

        $added = $this->service->enqueueEligibleServers();

        $this->assertSame(2, $added);
        $this->assertSame(2, $this->service->getQueueSize());
        $this->assertGreaterThan(0, $eligibleA);
        $this->assertGreaterThan(0, $eligibleB);
    }

    public function testDoesNotDuplicateQueuedJobs(): void
    {
        $this->createServer('srv-a', '10.0.0.34', 1, null);

        $first = $this->service->enqueueEligibleServers();
        $second = $this->service->enqueueEligibleServers();

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertSame(1, $this->service->getQueueSize());
    }

    public function testDoesNotRequeueWithinCooldownWindow(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.35', 1, null);
        $this->service->enqueueEligibleServers();
        $this->service->processNext();

        $added = $this->service->enqueueEligibleServers();
        $this->assertSame(0, $added);
        $this->assertSame(0, $this->service->getQueueSize());

        $lastCheckAt = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNotNull($lastCheckAt);
    }

    public function testRequeuesWhenLastCheckAtIsExpired(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.36', 1, null);
        $this->service->runCycle();

        $this->pdo->exec("UPDATE servers SET last_check_at = datetime('now', '-40 seconds') WHERE id = $serverId");
        $added = $this->service->enqueueEligibleServers();

        $this->assertSame(1, $added);
        $this->assertSame(1, $this->service->getQueueSize());
    }

    public function testRunCycleRespectsCooldownLogic(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.37', 1, null);

        $firstProcessed = $this->service->runCycle();
        $secondProcessed = $this->service->runCycle();

        $this->assertSame(1, $firstProcessed);
        $this->assertSame(0, $secondProcessed);

        $this->pdo->exec("UPDATE servers SET last_check_at = datetime('now', '-40 seconds') WHERE id = $serverId");
        $thirdProcessed = $this->service->runCycle();
        $this->assertSame(1, $thirdProcessed);
    }

    private function createServer(string $name, string $ipAddress, int $isActive, ?string $lastCheckAtExpression): int
    {
        $lastCheckAt = $lastCheckAtExpression === null ? 'NULL' : $lastCheckAtExpression;
        $this->pdo->exec(
            "INSERT INTO servers (name, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, last_check_at, created_at, updated_at) VALUES ('$name', '$ipAddress', $isActive, 1, 8, 16, 100, 60, 30, 90, 90, 90, 90, 1, 1, 1, 1, $lastCheckAt, datetime('now'), datetime('now'))"
        );

        return (int) $this->pdo->lastInsertId();
    }
}
