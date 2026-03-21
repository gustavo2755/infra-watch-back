<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Server;
use PDOException;

/**
 * Repository for monitoring queue operational queries.
 */
final class MonitoringQueueRepository extends BaseRepository
{
    private const SERVER_COLUMNS = 'id, name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, last_check_at, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at';

    /**
     * @return list<Server>
     */
    public function listEligibleServers(int $cooldownSeconds = 30): array
    {
        $seconds = max(1, $cooldownSeconds);
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $cutoffExpr = $driver === 'sqlite'
            ? "datetime('now', '-{$seconds} seconds')"
            : "DATE_SUB(NOW(), INTERVAL {$seconds} SECOND)";

        $rows = $this->fetchAll(
            'SELECT ' . self::SERVER_COLUMNS . " FROM servers WHERE is_active = 1 AND deleted_at IS NULL AND (last_check_at IS NULL OR last_check_at <= $cutoffExpr) ORDER BY id"
        );

        return array_map(fn (array $row) => $this->mapRowToServer($row), $rows);
    }

    /**
     * @throws PDOException
     */
    public function touchLastCheckAt(int $serverId): void
    {
        $now = $this->now();
        $this->execute("UPDATE servers SET last_check_at = $now WHERE id = ? AND deleted_at IS NULL", [$serverId]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToServer(array $row): Server
    {
        return new Server(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['name'] ?? null,
            $row['description'] ?? null,
            $row['ip_address'] ?? null,
            isset($row['is_active']) ? (bool) $row['is_active'] : true,
            isset($row['monitor_resources']) ? (bool) $row['monitor_resources'] : true,
            isset($row['cpu_total']) ? (float) $row['cpu_total'] : null,
            isset($row['ram_total']) ? (float) $row['ram_total'] : null,
            isset($row['disk_total']) ? (float) $row['disk_total'] : null,
            isset($row['check_interval_seconds']) ? (int) $row['check_interval_seconds'] : null,
            $row['last_check_at'] ?? null,
            isset($row['retention_days']) ? (int) $row['retention_days'] : null,
            isset($row['cpu_alert_threshold']) ? (float) $row['cpu_alert_threshold'] : null,
            isset($row['ram_alert_threshold']) ? (float) $row['ram_alert_threshold'] : null,
            isset($row['disk_alert_threshold']) ? (float) $row['disk_alert_threshold'] : null,
            isset($row['bandwidth_alert_threshold']) ? (float) $row['bandwidth_alert_threshold'] : null,
            isset($row['alert_cpu_enabled']) ? (bool) $row['alert_cpu_enabled'] : true,
            isset($row['alert_ram_enabled']) ? (bool) $row['alert_ram_enabled'] : true,
            isset($row['alert_disk_enabled']) ? (bool) $row['alert_disk_enabled'] : true,
            isset($row['alert_bandwidth_enabled']) ? (bool) $row['alert_bandwidth_enabled'] : true,
            isset($row['created_by']) ? (int) $row['created_by'] : null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
