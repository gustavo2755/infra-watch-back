<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\ServerServiceInterface;
use App\Exceptions\HttpException;
use App\Repositories\MonitoringLogRepository;
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
            new ServerServiceCheckRepository($this->pdo),
            new MonitoringLogRepository($this->pdo),
            $this->pdo
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

    public function testDeleteRemovesMonitoringLogsAndSoftDeletesLinks(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('DeleteWithLogs', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");
        $serverId = (int) $this->pdo->lastInsertId();
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();

        $this->pdo->exec("INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES ($serverId, $serviceCheckId, datetime('now'), datetime('now'))");
        $this->pdo->exec("INSERT INTO monitoring_logs (server_id, checked_at, is_up, cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type, error_message, sent_to_email, created_at, updated_at) VALUES ($serverId, datetime('now'), 1, 10, 10, 10, 10, 0, NULL, NULL, NULL, datetime('now'), datetime('now'))");
        $logId = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO monitoring_log_service_checks (monitoring_log_id, service_check_id, is_running, output_message, created_at, updated_at) VALUES ($logId, $serviceCheckId, 1, 'ok', datetime('now'), datetime('now'))");

        $this->service->delete($serverId);

        $serverDeletedAt = $this->pdo->query("SELECT deleted_at FROM servers WHERE id = $serverId")->fetchColumn();
        $pivotDeletedAt = $this->pdo->query("SELECT deleted_at FROM server_service_checks WHERE server_id = $serverId AND service_check_id = $serviceCheckId")->fetchColumn();
        $logsCount = $this->pdo->query("SELECT COUNT(*) FROM monitoring_logs WHERE server_id = $serverId")->fetchColumn();
        $childrenCount = $this->pdo->query("SELECT COUNT(*) FROM monitoring_log_service_checks WHERE monitoring_log_id = $logId")->fetchColumn();

        $this->assertNotNull($serverDeletedAt);
        $this->assertNotNull($pivotDeletedAt);
        $this->assertSame(0, (int) $logsCount);
        $this->assertSame(0, (int) $childrenCount);
    }

    public function testDeleteRollsBackWhenLogsDeletionFails(): void
    {
        $userId = (int) $this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        if ($userId === 0) {
            $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'u@t.com', 'h', datetime('now'), datetime('now'))");
            $userId = (int) $this->pdo->lastInsertId();
        }

        $this->pdo->exec("INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES ('RollbackDelete', null, '1.1.1.1', 1, 1, 2, 4, 50, 60, 30, 90, 90, 90, 100, 1, 1, 1, 1, $userId, datetime('now'), datetime('now'))");
        $serverId = (int) $this->pdo->lastInsertId();
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();

        $this->pdo->exec("INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES ($serverId, $serviceCheckId, datetime('now'), datetime('now'))");
        $this->pdo->exec("INSERT INTO monitoring_logs (server_id, checked_at, is_up, cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type, error_message, sent_to_email, created_at, updated_at) VALUES ($serverId, datetime('now'), 1, 10, 10, 10, 10, 0, NULL, NULL, NULL, datetime('now'), datetime('now'))");

        $this->pdo->exec("CREATE TRIGGER fail_monitoring_logs_delete BEFORE DELETE ON monitoring_logs BEGIN SELECT RAISE(ABORT, 'forced failure'); END");

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('It was not possible to complete deletion');

        try {
            $this->service->delete($serverId);
        } finally {
            $serverDeletedAt = $this->pdo->query("SELECT deleted_at FROM servers WHERE id = $serverId")->fetchColumn();
            $pivotDeletedAt = $this->pdo->query("SELECT deleted_at FROM server_service_checks WHERE server_id = $serverId AND service_check_id = $serviceCheckId")->fetchColumn();
            $this->assertNull($serverDeletedAt);
            $this->assertNull($pivotDeletedAt);
        }
    }
}
