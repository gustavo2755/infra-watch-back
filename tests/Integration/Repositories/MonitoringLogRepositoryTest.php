<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Models\MonitoringLog;
use App\Repositories\MonitoringLogRepository;
use Tests\DatabaseTestCase;

final class MonitoringLogRepositoryTest extends DatabaseTestCase
{
    private MonitoringLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MonitoringLogRepository($this->pdo);
    }

    public function testCreateAndFindById(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');
        $checkedAt = '2026-01-01 10:00:00';
        $log = new MonitoringLog(null, $serverId, $checkedAt, true, 10.5, 20.5, 30.5, 40.5, false, null, null, null);

        $id = $this->repository->create($log);
        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id, $found->getId());
        $this->assertSame($serverId, $found->getServerId());
        $this->assertSame($checkedAt, $found->getCheckedAt());
        $this->assertTrue($found->getIsUp());
    }

    public function testListByServerId(): void
    {
        $serverA = $this->createServer('srv-a', '10.0.0.1');
        $serverB = $this->createServer('srv-b', '10.0.0.2');

        $this->repository->create(new MonitoringLog(null, $serverA, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverA, '2026-01-01 10:05:00', true, 11, 11, 11, 11, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverB, '2026-01-01 10:10:00', false, 12, 12, 12, 12, true, 'cpu', 'high', 'ops@example.com'));

        $logs = $this->repository->listByServerId($serverA);

        $this->assertCount(2, $logs);
        $this->assertSame($serverA, $logs[0]->getServerId());
        $this->assertSame($serverA, $logs[1]->getServerId());
    }

    public function testListRecent(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:10:00', true, 20, 20, 20, 20, false, null, null, null));

        $recent = $this->repository->listRecent(1);

        $this->assertCount(1, $recent);
        $this->assertSame('2026-01-01 10:10:00', $recent[0]->getCheckedAt());
    }

    public function testListByPeriod(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 09:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 20, 20, 20, 20, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 11:00:00', true, 30, 30, 30, 30, false, null, null, null));

        $logs = $this->repository->listByPeriod('2026-01-01 09:30:00', '2026-01-01 10:30:00');

        $this->assertCount(1, $logs);
        $this->assertSame('2026-01-01 10:00:00', $logs[0]->getCheckedAt());
    }

    public function testListAlerts(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:05:00', false, 80, 20, 20, 20, true, 'cpu', 'cpu high', 'ops@example.com'));

        $alerts = $this->repository->listAlerts();

        $this->assertCount(1, $alerts);
        $this->assertTrue($alerts[0]->getIsAlert());
        $this->assertSame('cpu', $alerts[0]->getAlertType());
    }

    public function testReturnsEmptyWhenNoRecords(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->assertSame([], $this->repository->listByServerId($serverId));
        $this->assertSame([], $this->repository->listAlerts($serverId));
        $this->assertSame([], $this->repository->listByPeriod('2026-01-01 00:00:00', '2026-01-01 23:59:59', $serverId));
    }

    public function testDeleteOlderThan(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 09:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 11:00:00', true, 20, 20, 20, 20, false, null, null, null));

        $deleted = $this->repository->deleteOlderThan('2026-01-01 10:00:00');
        $remaining = $this->repository->listByServerId($serverId);

        $this->assertSame(1, $deleted);
        $this->assertCount(1, $remaining);
        $this->assertSame('2026-01-01 11:00:00', $remaining[0]->getCheckedAt());
    }

    public function testListForDashboard(): void
    {
        $serverId = $this->createServer('srv-a', '10.0.0.1');

        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverId, '2026-01-01 10:10:00', true, 20, 20, 20, 20, false, null, null, null));

        $dashboard = $this->repository->listForDashboard($serverId, 1);

        $this->assertCount(1, $dashboard);
        $this->assertSame('2026-01-01 10:10:00', $dashboard[0]->getCheckedAt());
    }

    public function testPaginatedMethodsAndCountMethods(): void
    {
        $serverA = $this->createServer('srv-pa', '10.0.1.1');
        $serverB = $this->createServer('srv-pb', '10.0.1.2');

        $this->repository->create(new MonitoringLog(null, $serverA, '2026-01-01 10:00:00', true, 10, 10, 10, 10, false, null, null, null));
        $this->repository->create(new MonitoringLog(null, $serverA, '2026-01-01 10:01:00', true, 11, 11, 11, 11, true, 'cpu', null, null));
        $this->repository->create(new MonitoringLog(null, $serverB, '2026-01-01 10:02:00', true, 12, 12, 12, 12, true, 'ram', null, null));

        $byServer = $this->repository->listByServerIdPaginated($serverA, 1, 1);
        $this->assertCount(1, $byServer);
        $this->assertSame(2, $this->repository->countByServerId($serverA));

        $recent = $this->repository->listRecentPaginated(1, 2);
        $this->assertCount(2, $recent);
        $this->assertSame(3, $this->repository->countAll());

        $period = $this->repository->listByPeriodPaginated('2026-01-01 09:59:00', '2026-01-01 10:01:59', null, 1, 10);
        $this->assertCount(2, $period);
        $this->assertSame(2, $this->repository->countByPeriod('2026-01-01 09:59:00', '2026-01-01 10:01:59'));

        $alerts = $this->repository->listAlertsPaginated(null, 1, 10);
        $this->assertCount(2, $alerts);
        $this->assertSame(2, $this->repository->countAlerts());

        $dashboard = $this->repository->listForDashboardPaginated($serverA, 1, 1);
        $this->assertCount(1, $dashboard);
    }

    private function createServer(string $name, string $ipAddress): int
    {
        $this->pdo->exec("INSERT INTO servers (name, ip_address, is_active, created_at, updated_at) VALUES ('$name', '$ipAddress', 1, datetime('now'), datetime('now'))");

        return (int) $this->pdo->lastInsertId();
    }
}
