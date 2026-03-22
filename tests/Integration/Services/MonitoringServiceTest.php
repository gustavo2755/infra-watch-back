<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Exceptions\HttpException;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Repositories\MonitoringQueueRepository;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Services\MonitoringService;
use Tests\DatabaseTestCase;

final class MonitoringServiceTest extends DatabaseTestCase
{
    private MonitoringService $service;
    private ServerRepository $serverRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MonitoringService(
            new MonitoringLogRepository($this->pdo),
            new MonitoringLogServiceCheckRepository($this->pdo),
            new ServerServiceCheckRepository($this->pdo),
            new MonitoringQueueRepository($this->pdo)
        );
        $this->serverRepository = new ServerRepository($this->pdo);
    }

    public function testGeneratesMetricsWithinExpectedRange(): void
    {
        $serverId = $this->createServerWithThresholds(100, 100, 100, 100);
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $this->service->processServer($server);

        $row = $this->pdo->query("SELECT cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent FROM monitoring_logs WHERE server_id = $serverId ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);

        $this->assertGreaterThanOrEqual(0.0, (float) $row['cpu_usage_percent']);
        $this->assertLessThanOrEqual(50.0, (float) $row['cpu_usage_percent']);
        $this->assertGreaterThanOrEqual(0.0, (float) $row['ram_usage_percent']);
        $this->assertLessThanOrEqual(50.0, (float) $row['ram_usage_percent']);
        $this->assertGreaterThanOrEqual(0.0, (float) $row['disk_usage_percent']);
        $this->assertLessThanOrEqual(50.0, (float) $row['disk_usage_percent']);
        $this->assertGreaterThanOrEqual(0.0, (float) $row['bandwidth_usage_percent']);
        $this->assertLessThanOrEqual(50.0, (float) $row['bandwidth_usage_percent']);
    }

    public function testCreatesMonitoringLogAndServiceCheckResults(): void
    {
        $serverId = $this->createServerWithThresholds(100, 100, 100, 100);
        $this->linkServiceCheck($serverId, 'nginx');
        $this->linkServiceCheck($serverId, 'mysql');
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $log = $this->service->processServer($server);

        $this->assertNotNull($log->getId());
        $logId = (int) $log->getId();
        $results = $this->pdo->query("SELECT COUNT(*) FROM monitoring_log_service_checks WHERE monitoring_log_id = $logId")->fetchColumn();
        $this->assertSame(2, (int) $results);
    }

    public function testUpdatesLastCheckAtAfterProcessing(): void
    {
        $serverId = $this->createServerWithThresholds(100, 100, 100, 100);
        $before = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNull($before);

        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);
        $this->service->processServer($server);

        $after = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNotNull($after);
    }

    public function testLogWithoutAlertWhenThresholdsAreNotExceeded(): void
    {
        $serverId = $this->createServerWithThresholds(100, 100, 100, 100);
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $this->service->processServer($server);

        $isAlert = $this->pdo->query("SELECT is_alert FROM monitoring_logs WHERE server_id = $serverId ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertSame(0, (int) $isAlert);
    }

    public function testLogWithAlertWhenThresholdsAreExceeded(): void
    {
        $serverId = $this->createServerWithThresholds(-1, -1, -1, -1);
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $this->service->processServer($server);

        $isAlert = $this->pdo->query("SELECT is_alert FROM monitoring_logs WHERE server_id = $serverId ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertSame(1, (int) $isAlert);
    }

    public function testMonitorResourcesFalseDoesNotGenerateMetrics(): void
    {
        $serverId = $this->createServerWithoutResourceMonitoring();
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $log = $this->service->processServer($server);

        $this->assertNotNull($log->getId());
        $this->assertTrue($log->getIsUp());

        $row = $this->pdo->query("SELECT cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type FROM monitoring_logs WHERE id = {$log->getId()}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);
        $this->assertNull($row['cpu_usage_percent']);
        $this->assertNull($row['ram_usage_percent']);
        $this->assertNull($row['disk_usage_percent']);
        $this->assertNull($row['bandwidth_usage_percent']);
        $this->assertSame(0, (int) $row['is_alert']);
        $this->assertNull($row['alert_type']);
    }

    public function testMonitorResourcesFalseStillCreatesLogWithIsUp(): void
    {
        $serverId = $this->createServerWithoutResourceMonitoring();
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $log = $this->service->processServer($server);

        $this->assertNotNull($log->getId());
        $row = $this->pdo->query("SELECT checked_at, is_up FROM monitoring_logs WHERE id = {$log->getId()}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);
        $this->assertNotNull($row['checked_at']);
        $this->assertSame(1, (int) $row['is_up']);
    }

    public function testMonitorResourcesFalseStillUpdatesLastCheckAt(): void
    {
        $serverId = $this->createServerWithoutResourceMonitoring();
        $before = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNull($before);

        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);
        $this->service->processServer($server);

        $after = $this->pdo->query("SELECT last_check_at FROM servers WHERE id = $serverId")->fetchColumn();
        $this->assertNotNull($after);
    }

    public function testMonitorResourcesFalseStillCreatesServiceCheckResults(): void
    {
        $serverId = $this->createServerWithoutResourceMonitoring();
        $this->linkServiceCheck($serverId, 'nginx');
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $log = $this->service->processServer($server);

        $logId = (int) $log->getId();
        $count = $this->pdo->query("SELECT COUNT(*) FROM monitoring_log_service_checks WHERE monitoring_log_id = $logId")->fetchColumn();
        $this->assertSame(1, (int) $count);
    }

    public function testThresholdsOnlyTriggerAlertWhenMonitorResourcesIsTrue(): void
    {
        $serverWithResources = $this->createServerWithThresholds(-1, -1, -1, -1);
        $serverWithoutResources = $this->createServerWithoutResourceMonitoring(-1, -1, -1, -1);

        $serverA = $this->serverRepository->findById($serverWithResources);
        $this->assertNotNull($serverA);
        $logA = $this->service->processServer($serverA);
        $isAlertA = $this->pdo->query("SELECT is_alert FROM monitoring_logs WHERE id = {$logA->getId()}")->fetchColumn();
        $this->assertSame(1, (int) $isAlertA);

        $serverB = $this->serverRepository->findById($serverWithoutResources);
        $this->assertNotNull($serverB);
        $logB = $this->service->processServer($serverB);
        $isAlertB = $this->pdo->query("SELECT is_alert FROM monitoring_logs WHERE id = {$logB->getId()}")->fetchColumn();
        $this->assertSame(0, (int) $isAlertB);
    }

    public function testThrowsWhenPersistenceFails(): void
    {
        $serverId = $this->createServerWithThresholds(100, 100, 100, 100);
        $server = $this->serverRepository->findById($serverId);
        $this->assertNotNull($server);

        $this->pdo->exec('DROP TABLE monitoring_logs');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to persist monitoring data');

        $this->service->processServer($server);
    }

    private function createServerWithThresholds(float $cpuThreshold, float $ramThreshold, float $diskThreshold, float $bandwidthThreshold): int
    {
        $this->pdo->exec("INSERT INTO servers (name, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_at, updated_at) VALUES ('srv-monitor', '10.0.0.20', 1, 1, 8, 16, 100, 60, 30, $cpuThreshold, $ramThreshold, $diskThreshold, $bandwidthThreshold, 1, 1, 1, 1, datetime('now'), datetime('now'))");

        return (int) $this->pdo->lastInsertId();
    }

    private function createServerWithoutResourceMonitoring(float $cpuThreshold = 100, float $ramThreshold = 100, float $diskThreshold = 100, float $bandwidthThreshold = 100): int
    {
        $this->pdo->exec("INSERT INTO servers (name, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_at, updated_at) VALUES ('srv-noresource', '10.0.0.30', 1, 0, 8, 16, 100, 60, 30, $cpuThreshold, $ramThreshold, $diskThreshold, $bandwidthThreshold, 1, 1, 1, 1, datetime('now'), datetime('now'))");

        return (int) $this->pdo->lastInsertId();
    }

    private function linkServiceCheck(int $serverId, string $slug): void
    {
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = '$slug'")->fetchColumn();
        $this->pdo->exec("INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES ($serverId, $serviceCheckId, datetime('now'), datetime('now'))");
    }
}
