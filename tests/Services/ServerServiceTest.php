<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\ServerServiceInterface;
use App\Exceptions\HttpException;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Repositories\UserRepository;
use App\Services\ServerService;
use Tests\DatabaseTestCase;

final class ServerServiceTest extends DatabaseTestCase
{
    private ServerServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServerService(
            new ServerRepository($this->pdo),
            new UserRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo)
        );
    }

    public function testCreateWithValidPayload(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $data = [
            'name' => 'New Server',
            'description' => 'Test desc',
            'ip_address' => '192.168.1.10',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 4.0,
            'ram_total' => 8.0,
            'disk_total' => 100.0,
            'check_interval_seconds' => 60,
            'last_check_at' => null,
            'retention_days' => 30,
            'cpu_alert_threshold' => 90,
            'ram_alert_threshold' => 90,
            'disk_alert_threshold' => 90,
            'bandwidth_alert_threshold' => 100,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
            'created_by' => $userId,
        ];

        $server = $this->service->create($data);

        $this->assertNotNull($server->getId());
        $this->assertSame('New Server', $server->getName());
        $this->assertSame('192.168.1.10', $server->getIpAddress());
    }

    public function testUpdateWithValidData(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Old', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $id = (int) $this->pdo->lastInsertId();

        $server = $this->service->update($id, ['name' => 'Updated Name', 'ip_address' => '10.0.0.1']);

        $this->assertSame($id, $server->getId());
        $this->assertSame('Updated Name', $server->getName());
        $this->assertSame('10.0.0.1', $server->getIpAddress());
    }

    public function testFindByIdExisting(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Find Me', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $id = (int) $this->pdo->lastInsertId();

        $server = $this->service->findById($id);

        $this->assertSame($id, $server->getId());
        $this->assertSame('Find Me', $server->getName());
    }

    public function testFindByIdNonexistent(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->findById(99999);
    }

    public function testList(): void
    {
        $servers = $this->service->list();

        $this->assertIsArray($servers);
    }

    public function testFilterByName(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Filter Alpha', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $results = $this->service->filterByName('Filter');

        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testFilterByIsActive(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Inactive', null, '1.1.1.1', 0, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $inactive = $this->service->filterByIsActive(false);

        $this->assertGreaterThanOrEqual(1, count($inactive));
    }

    public function testExceptionWhenOperatingNonexistentResource(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->update(99999, ['name' => 'X']);
    }

    public function testCreateThrowsWhenUserNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('User not found');

        $this->service->create([
            'name' => 'S',
            'description' => null,
            'ip_address' => '1.1.1.1',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 2.0,
            'ram_total' => 4.0,
            'disk_total' => 50.0,
            'check_interval_seconds' => 60,
            'last_check_at' => null,
            'retention_days' => 30,
            'cpu_alert_threshold' => 90,
            'ram_alert_threshold' => 90,
            'disk_alert_threshold' => 90,
            'bandwidth_alert_threshold' => 100,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
            'created_by' => 99999,
        ]);
    }

    public function testDeleteSuccess(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('ToDelete', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $id = (int) $this->pdo->lastInsertId();

        $this->service->delete($id);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->findById($id);
    }

    public function testDeleteThrows404WhenNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->delete(99999);
    }

    public function testDeleteWithLinkedServiceCheck(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('Linked', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");

        $serverId = (int) $this->pdo->lastInsertId();
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();

        $this->pdo->exec("INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES ($serverId, $serviceCheckId, datetime('now'), datetime('now'))");

        $this->service->delete($serverId);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Server not found');

        $this->service->findById($serverId);
    }
}
