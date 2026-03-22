<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Repositories\MonitoringQueueRepository;
use Tests\DatabaseTestCase;

final class MonitoringQueueRepositoryTest extends DatabaseTestCase
{
    private MonitoringQueueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MonitoringQueueRepository($this->pdo);
    }

    public function testListEligibleServersWhenLastCheckAtIsNull(): void
    {
        $serverId = $this->createServer('srv-null', '10.0.0.10', 1, null, null);

        $eligible = $this->repository->listEligibleServers(30);
        $ids = array_map(fn ($server) => $server->getId(), $eligible);

        $this->assertContains($serverId, $ids);
    }

    public function testListEligibleServersWhenLastCheckAtIsExpired(): void
    {
        $serverId = $this->createServer('srv-expired', '10.0.0.11', 1, "datetime('now', '-40 seconds')", null);

        $eligible = $this->repository->listEligibleServers(30);
        $ids = array_map(fn ($server) => $server->getId(), $eligible);

        $this->assertContains($serverId, $ids);
    }

    public function testDoesNotReturnInactiveServers(): void
    {
        $inactiveId = $this->createServer('srv-inactive', '10.0.0.12', 0, null, null);

        $eligible = $this->repository->listEligibleServers(30);
        $ids = array_map(fn ($server) => $server->getId(), $eligible);

        $this->assertNotContains($inactiveId, $ids);
    }

    public function testDoesNotReturnServersInsideCooldownWindow(): void
    {
        $recentId = $this->createServer('srv-recent', '10.0.0.13', 1, "datetime('now', '-10 seconds')", null);

        $eligible = $this->repository->listEligibleServers(30);
        $ids = array_map(fn ($server) => $server->getId(), $eligible);

        $this->assertNotContains($recentId, $ids);
    }

    public function testListEligibleServersIncludesServerWithMonitorResourcesFalse(): void
    {
        $serverId = $this->createServer('srv-no-res', '10.0.0.15', 1, null, null, 0);

        $eligible = $this->repository->listEligibleServers(30);
        $ids = array_map(fn ($server) => $server->getId(), $eligible);

        $this->assertContains($serverId, $ids);
    }

    public function testTouchLastCheckAtUpdatesTimestamp(): void
    {
        $serverId = $this->createServer('srv-touch', '10.0.0.14', 1, null, null);

        $before = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNull($before);

        $this->repository->touchLastCheckAt($serverId);

        $after = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNotFalse($after);
        $this->assertNotNull($after);
    }

    private function createServer(string $name, string $ipAddress, int $isActive, ?string $lastCheckAtExpression, ?string $deletedAtExpression, int $monitorResources = 1): int
    {
        $lastCheckAt = $lastCheckAtExpression === null ? 'NULL' : $lastCheckAtExpression;
        $deletedAt = $deletedAtExpression === null ? 'NULL' : $deletedAtExpression;
        $this->pdo->exec(
            "INSERT INTO servers (name, ip_address, is_active, monitor_resources, last_check_at, created_at, updated_at, deleted_at) VALUES ('$name', '$ipAddress', $isActive, $monitorResources, $lastCheckAt, datetime('now'), datetime('now'), $deletedAt)"
        );

        return (int) $this->pdo->lastInsertId();
    }
}
