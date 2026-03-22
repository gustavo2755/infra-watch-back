<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\MonitoringLog;
use PDOException;

/**
 * Repository for monitoring log persistence and queries.
 */
final class MonitoringLogRepository extends BaseRepository
{
    private const COLUMNS = 'id, server_id, checked_at, is_up, cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type, error_message, sent_to_email, created_at, updated_at';

    /**
     * @throws PDOException
     */
    public function create(MonitoringLog $log): int
    {
        $now = $this->now();
        $this->execute(
            "INSERT INTO monitoring_logs (server_id, checked_at, is_up, cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent, is_alert, alert_type, error_message, sent_to_email, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $now, $now)",
            [
                $log->getServerId(),
                $log->getCheckedAt(),
                $log->getIsUp() ? 1 : 0,
                $log->getCpuUsagePercent(),
                $log->getRamUsagePercent(),
                $log->getDiskUsagePercent(),
                $log->getBandwidthUsagePercent(),
                $log->getIsAlert() ? 1 : 0,
                $log->getAlertType(),
                $log->getErrorMessage(),
                $log->getSentToEmail(),
            ]
        );

        return $this->lastInsertId();
    }

    public function findById(int $id): ?MonitoringLog
    {
        $row = $this->fetchOne('SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE id = ?', [$id]);

        return $row ? $this->mapRowToMonitoringLog($row) : null;
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listByServerId(int $serverId): array
    {
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? ORDER BY checked_at DESC, id DESC', [$serverId]);

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listByServerIdPaginated(int $serverId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
            [$serverId, $perPage, $offset]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    public function countByServerId(int $serverId): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM monitoring_logs WHERE server_id = ?', [$serverId]);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listRecent(int $limit = 20): array
    {
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM monitoring_logs ORDER BY checked_at DESC, id DESC LIMIT ?', [$limit]);

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listRecentPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    public function countAll(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM monitoring_logs');

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listByPeriod(string $from, string $to, ?int $serverId = null): array
    {
        if ($serverId === null) {
            $rows = $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE checked_at BETWEEN ? AND ? ORDER BY checked_at DESC, id DESC',
                [$from, $to]
            );

            return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
        }

        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? AND checked_at BETWEEN ? AND ? ORDER BY checked_at DESC, id DESC',
            [$serverId, $from, $to]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listByPeriodPaginated(string $from, string $to, ?int $serverId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        if ($serverId === null) {
            $rows = $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE checked_at BETWEEN ? AND ? ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
                [$from, $to, $perPage, $offset]
            );

            return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
        }

        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? AND checked_at BETWEEN ? AND ? ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
            [$serverId, $from, $to, $perPage, $offset]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    public function countByPeriod(string $from, string $to, ?int $serverId = null): int
    {
        if ($serverId === null) {
            $row = $this->fetchOne(
                'SELECT COUNT(*) AS total FROM monitoring_logs WHERE checked_at BETWEEN ? AND ?',
                [$from, $to]
            );

            return (int) ($row['total'] ?? 0);
        }

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM monitoring_logs WHERE server_id = ? AND checked_at BETWEEN ? AND ?',
            [$serverId, $from, $to]
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listAlerts(?int $serverId = null): array
    {
        if ($serverId === null) {
            $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE is_alert = 1 ORDER BY checked_at DESC, id DESC');

            return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
        }

        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE is_alert = 1 AND server_id = ? ORDER BY checked_at DESC, id DESC', [$serverId]);

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listAlertsPaginated(?int $serverId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        if ($serverId === null) {
            $rows = $this->fetchAll(
                'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE is_alert = 1 ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
                [$perPage, $offset]
            );

            return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
        }

        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE is_alert = 1 AND server_id = ? ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
            [$serverId, $perPage, $offset]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    public function countAlerts(?int $serverId = null): int
    {
        if ($serverId === null) {
            $row = $this->fetchOne('SELECT COUNT(*) AS total FROM monitoring_logs WHERE is_alert = 1');

            return (int) ($row['total'] ?? 0);
        }

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM monitoring_logs WHERE is_alert = 1 AND server_id = ?',
            [$serverId]
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @throws PDOException
     */
    public function deleteOlderThan(string $cutoff): int
    {
        $this->execute(
            'DELETE FROM monitoring_log_service_checks WHERE monitoring_log_id IN (SELECT id FROM monitoring_logs WHERE checked_at < ?)',
            [$cutoff]
        );
        $stmt = $this->execute('DELETE FROM monitoring_logs WHERE checked_at < ?', [$cutoff]);

        return $stmt->rowCount();
    }

    /**
     * @throws PDOException
     */
    public function deleteOlderThanByServer(int $serverId, string $cutoff): int
    {
        $this->execute(
            'DELETE FROM monitoring_log_service_checks WHERE monitoring_log_id IN (SELECT id FROM monitoring_logs WHERE server_id = ? AND checked_at < ?)',
            [$serverId, $cutoff]
        );
        $stmt = $this->execute(
            'DELETE FROM monitoring_logs WHERE server_id = ? AND checked_at < ?',
            [$serverId, $cutoff]
        );

        return $stmt->rowCount();
    }

    /**
     * @throws PDOException
     */
    public function deleteByServerId(int $serverId): int
    {
        $this->execute(
            'DELETE FROM monitoring_log_service_checks WHERE monitoring_log_id IN (SELECT id FROM monitoring_logs WHERE server_id = ?)',
            [$serverId]
        );
        $stmt = $this->execute('DELETE FROM monitoring_logs WHERE server_id = ?', [$serverId]);

        return $stmt->rowCount();
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listForDashboard(int $serverId, int $limit = 100): array
    {
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? ORDER BY checked_at DESC, id DESC LIMIT ?',
            [$serverId, $limit]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @return list<MonitoringLog>
     */
    public function listForDashboardPaginated(int $serverId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_logs WHERE server_id = ? ORDER BY checked_at DESC, id DESC LIMIT ? OFFSET ?',
            [$serverId, $perPage, $offset]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLog($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToMonitoringLog(array $row): MonitoringLog
    {
        return new MonitoringLog(
            isset($row['id']) ? (int) $row['id'] : null,
            isset($row['server_id']) ? (int) $row['server_id'] : null,
            $row['checked_at'] ?? null,
            isset($row['is_up']) ? (bool) $row['is_up'] : false,
            isset($row['cpu_usage_percent']) ? (float) $row['cpu_usage_percent'] : null,
            isset($row['ram_usage_percent']) ? (float) $row['ram_usage_percent'] : null,
            isset($row['disk_usage_percent']) ? (float) $row['disk_usage_percent'] : null,
            isset($row['bandwidth_usage_percent']) ? (float) $row['bandwidth_usage_percent'] : null,
            isset($row['is_alert']) ? (bool) $row['is_alert'] : false,
            $row['alert_type'] ?? null,
            $row['error_message'] ?? null,
            $row['sent_to_email'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
