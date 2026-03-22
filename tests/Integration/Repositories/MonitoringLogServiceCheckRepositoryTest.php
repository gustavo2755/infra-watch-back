<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Models\MonitoringLog;
use App\Models\MonitoringLogServiceCheck;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use Tests\DatabaseTestCase;

final class MonitoringLogServiceCheckRepositoryTest extends DatabaseTestCase
{
    private MonitoringLogRepository $logRepository;
    private MonitoringLogServiceCheckRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logRepository = new MonitoringLogRepository($this->pdo);
        $this->repository = new MonitoringLogServiceCheckRepository($this->pdo);
    }

    public function testCreateAndListByMonitoringLogId(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');
        $monitoringLogId = $this->logRepository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();

        $resultId = $this->repository->create(new MonitoringLogServiceCheck(null, $monitoringLogId, $serviceCheckId, true, 'running'));
        $results = $this->repository->listByMonitoringLogId($monitoringLogId);

        $this->assertGreaterThan(0, $resultId);
        $this->assertCount(1, $results);
        $this->assertSame($monitoringLogId, $results[0]->getMonitoringLogId());
        $this->assertSame($serviceCheckId, $results[0]->getServiceCheckId());
        $this->assertTrue($results[0]->getIsRunning());
        $this->assertSame('running', $results[0]->getOutputMessage());
    }

    public function testListByMonitoringLogIdReturnsEmptyWhenNoRecords(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');
        $monitoringLogId = $this->logRepository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));

        $results = $this->repository->listByMonitoringLogId($monitoringLogId);

        $this->assertSame([], $results);
    }

    private function createServer(string $name, string $ipAddress): int
    {
        $this->pdo->exec("INSERT INTO servers (name, ip_address, is_active, created_at, updated_at) VALUES ('$name', '$ipAddress', 1, datetime('now'), datetime('now'))");

        return (int) $this->pdo->lastInsertId();
    }
}
