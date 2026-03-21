<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Server;
use PDOException;

/**
 * Repository for server persistence and queries.
 */
final class ServerRepository extends BaseRepository
{
    private const COLUMNS = 'id, name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, last_check_at, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at';

    public function findById(int $id): ?Server
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM servers WHERE id = ? AND deleted_at IS NULL', [$id]);

        return $row ? $this->mapRowToServer($row) : null;
    }

    /**
     * @throws PDOException
     */
    public function create(Server $server): int
    {
        $now = $this->now();
        $this->execute(
            "INSERT INTO servers (name, description, ip_address, is_active, monitor_resources, cpu_total, ram_total, disk_total, check_interval_seconds, last_check_at, retention_days, cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $now, $now)",
            [
                $server->getName(),
                $server->getDescription(),
                $server->getIpAddress(),
                $server->getIsActive() ? 1 : 0,
                $server->getMonitorResources() ? 1 : 0,
                $server->getCpuTotal(),
                $server->getRamTotal(),
                $server->getDiskTotal(),
                $server->getCheckIntervalSeconds(),
                $server->getLastCheckAt(),
                $server->getRetentionDays(),
                $server->getCpuAlertThreshold(),
                $server->getRamAlertThreshold(),
                $server->getDiskAlertThreshold(),
                $server->getBandwidthAlertThreshold(),
                $server->getAlertCpuEnabled() ? 1 : 0,
                $server->getAlertRamEnabled() ? 1 : 0,
                $server->getAlertDiskEnabled() ? 1 : 0,
                $server->getAlertBandwidthEnabled() ? 1 : 0,
                $server->getCreatedBy(),
            ]
        );

        return $this->lastInsertId();
    }

    /**
     * @throws PDOException
     */
    public function update(Server $server): int
    {
        $now = $this->now();
        $this->execute(
            "UPDATE servers SET name = ?, description = ?, ip_address = ?, is_active = ?, monitor_resources = ?, cpu_total = ?, ram_total = ?, disk_total = ?, check_interval_seconds = ?, last_check_at = ?, retention_days = ?, cpu_alert_threshold = ?, ram_alert_threshold = ?, disk_alert_threshold = ?, bandwidth_alert_threshold = ?, alert_cpu_enabled = ?, alert_ram_enabled = ?, alert_disk_enabled = ?, alert_bandwidth_enabled = ?, created_by = ?, updated_at = $now WHERE id = ?",
            [
                $server->getName(),
                $server->getDescription(),
                $server->getIpAddress(),
                $server->getIsActive() ? 1 : 0,
                $server->getMonitorResources() ? 1 : 0,
                $server->getCpuTotal(),
                $server->getRamTotal(),
                $server->getDiskTotal(),
                $server->getCheckIntervalSeconds(),
                $server->getLastCheckAt(),
                $server->getRetentionDays(),
                $server->getCpuAlertThreshold(),
                $server->getRamAlertThreshold(),
                $server->getDiskAlertThreshold(),
                $server->getBandwidthAlertThreshold(),
                $server->getAlertCpuEnabled() ? 1 : 0,
                $server->getAlertRamEnabled() ? 1 : 0,
                $server->getAlertDiskEnabled() ? 1 : 0,
                $server->getAlertBandwidthEnabled() ? 1 : 0,
                $server->getCreatedBy(),
                $server->getId(),
            ]
        );

        return (int) $server->getId();
    }

    /**
     * @throws PDOException
     */
    public function delete(int $id): void
    {
        $now = $this->now();
        $this->execute("UPDATE servers SET deleted_at = $now WHERE id = ?", [$id]);
    }

    /**
     * @return list<Server>
     */
    public function list(): array
    {
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM servers WHERE deleted_at IS NULL ORDER BY id');

        return array_map(fn (array $r) => $this->mapRowToServer($r), $rows);
    }

    /**
     * @return list<Server>
     */
    public function filterByName(string $name): array
    {
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM servers WHERE name LIKE ? AND deleted_at IS NULL ORDER BY id', ['%' . $name . '%']);

        return array_map(fn (array $r) => $this->mapRowToServer($r), $rows);
    }

    /**
     * @return list<Server>
     */
    public function filterByIsActive(bool $isActive): array
    {
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM servers WHERE is_active = ? AND deleted_at IS NULL ORDER BY id', [$isActive ? 1 : 0]);

        return array_map(fn (array $r) => $this->mapRowToServer($r), $rows);
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
