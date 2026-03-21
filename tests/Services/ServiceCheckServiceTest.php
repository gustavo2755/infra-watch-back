<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\ServiceCheckServiceInterface;
use App\Exceptions\HttpException;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Repositories\ServiceCheckRepository;
use App\Services\ServiceCheckService;
use Tests\DatabaseTestCase;

final class ServiceCheckServiceTest extends DatabaseTestCase
{
    private ServiceCheckServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceCheckService(
            new ServiceCheckRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo),
            new ServerRepository($this->pdo)
        );
    }

    public function testCreateWithValidPayload(): void
    {
        $sc = $this->service->create([
            'name' => 'Redis',
            'slug' => 'redis',
            'description' => 'Cache server',
        ]);

        $this->assertNotNull($sc->getId());
        $this->assertSame('Redis', $sc->getName());
        $this->assertSame('redis', $sc->getSlug());
    }

    public function testUpdateWithValidData(): void
    {
        $this->pdo->exec("INSERT INTO service_checks (name, slug, description, created_at, updated_at) VALUES ('Old', 'old', 'Desc', datetime('now'), datetime('now'))");

        $id = (int) $this->pdo->lastInsertId();

        $sc = $this->service->update($id, ['name' => 'Updated', 'slug' => 'updated', 'description' => 'New desc']);

        $this->assertSame($id, $sc->getId());
        $this->assertSame('Updated', $sc->getName());
        $this->assertSame('updated', $sc->getSlug());
    }

    public function testFindByIdExisting(): void
    {
        $found = $this->service->findById(1);

        $this->assertNotNull($found);
        $this->assertSame(1, $found->getId());
    }

    public function testFindByIdNonexistent(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Service check not found');

        $this->service->findById(99999);
    }

    public function testFindBySlug(): void
    {
        $found = $this->service->findBySlug('nginx');

        $this->assertNotNull($found);
        $this->assertSame('nginx', $found->getSlug());
    }

    public function testList(): void
    {
        $list = $this->service->list();

        $this->assertIsArray($list);
        $this->assertGreaterThanOrEqual(4, count($list));
    }

    public function testAttachServiceCheckToServer(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Srv', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $serverId = (int) $this->pdo->lastInsertId();
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();

        $this->service->attachToServer($serverId, $serviceCheckId);

        $linked = $this->service->listByServerId($serverId);

        $this->assertCount(1, $linked);
        $this->assertSame('nginx', $linked[0]->getSlug());
    }

    public function testExceptionWhenAttachNonexistentServer(): void
    {
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks LIMIT 1")->fetchColumn();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->attachToServer(99999, $serviceCheckId);
    }

    public function testExceptionWhenAttachNonexistentServiceCheck(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Srv', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $serverId = (int) $this->pdo->lastInsertId();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Service check not found');

        $this->service->attachToServer($serverId, 99999);
    }

    public function testListByServerIdThrowsWhenServerNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->listByServerId(99999);
    }
}
