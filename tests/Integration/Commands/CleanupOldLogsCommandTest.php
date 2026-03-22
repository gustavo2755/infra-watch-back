<?php

declare(strict_types=1);

namespace Tests\Commands;

use App\Commands\CleanupOldLogsCommand;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\ServerRepository;
use Tests\DatabaseTestCase;

final class CleanupOldLogsCommandTest extends DatabaseTestCase
{
    public function testExecuteDeletesOnlyOldLogsByRetention(): void
    {
        $serverId = $this->createServer('cleanup-server', '10.0.20.1', 30);
        $oldLogId = $this->createMonitoringLog($serverId, "datetime('now', '-40 days')");
        $newLogId = $this->createMonitoringLog($serverId, "datetime('now', '-5 days')");
        $serviceCheckId = (int) $this->pdo->query("SELECT id FROM service_checks WHERE slug = 'nginx'")->fetchColumn();
        $this->createMonitoringLogServiceCheck($oldLogId, $serviceCheckId);
        $this->createMonitoringLogServiceCheck($newLogId, $serviceCheckId);

        $command = new CleanupOldLogsCommand(
            new ServerRepository($this->pdo),
            new MonitoringLogRepository($this->pdo),
            30
        );

        $deleted = $command->execute();

        $this->assertSame(1, $deleted);

        $oldExists = $this->pdo->query("SELECT COUNT(*) FROM monitoring_logs WHERE id = $oldLogId")->fetchColumn();
        $newExists = $this->pdo->query("SELECT COUNT(*) FROM monitoring_logs WHERE id = $newLogId")->fetchColumn();
        $oldChildExists = $this->pdo->query("SELECT COUNT(*) FROM monitoring_log_service_checks WHERE monitoring_log_id = $oldLogId")->fetchColumn();
        $newChildExists = $this->pdo->query("SELECT COUNT(*) FROM monitoring_log_service_checks WHERE monitoring_log_id = $newLogId")->fetchColumn();

        $this->assertSame(0, (int) $oldExists);
        $this->assertSame(1, (int) $newExists);
        $this->assertSame(0, (int) $oldChildExists);
        $this->assertSame(1, (int) $newChildExists);
    }

    private function createServer(string $name, string $ipAddress, int $retentionDays): int
    {
        $this->pdo->exec(
            "INSERT INTO servers (name, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_at, updated_at) VALUES ('$name', '$ipAddress', 1, 1, 8, 16, 100, 60, $retentionDays, 90, 90, 90, 90, 1, 1, 1, 1, datetime('now'), datetime('now'))"
        );

        return (int) $this->pdo->lastInsertId();
    }

    private function createMonitoringLog(int $serverId, string $checkedAtExpression): int
    {
        $this->pdo->exec(
            "INSERT INTO monitoring_logs (server_id, checked_at, is_up, cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type, error_message, sent_to_email, created_at, updated_at) VALUES ($serverId, $checkedAtExpression, 1, 10, 10, 10, 10, 0, NULL, NULL, NULL, datetime('now'), datetime('now'))"
        );

        return (int) $this->pdo->lastInsertId();
    }

    private function createMonitoringLogServiceCheck(int $logId, int $serviceCheckId): void
    {
        $this->pdo->exec(
            "INSERT INTO monitoring_log_service_checks (monitoring_log_id, service_check_id, is_running, output_message, created_at, updated_at) VALUES ($logId, $serviceCheckId, 1, 'ok', datetime('now'), datetime('now'))"
        );
    }
}
