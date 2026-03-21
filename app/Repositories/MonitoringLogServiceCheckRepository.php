<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\MonitoringLogServiceCheck;
use PDOException;

/**
 * Repository for monitoring log service check persistence and queries.
 */
final class MonitoringLogServiceCheckRepository extends BaseRepository
{
    private const COLUMNS = 'id, monitoring_log_id, service_check_id, is_running, output_message, created_at, updated_at';

    /**
     * @throws PDOException
     */
    public function create(MonitoringLogServiceCheck $result): int
    {
        $now = $this->now();
        $this->execute(
            "INSERT INTO monitoring_log_service_checks (monitoring_log_id, service_check_id, is_running, output_message, created_at, updated_at) VALUES (?, ?, ?, ?, $now, $now)",
            [
                $result->getMonitoringLogId(),
                $result->getServiceCheckId(),
                $result->getIsRunning() ? 1 : 0,
                $result->getOutputMessage(),
            ]
        );

        return $this->lastInsertId();
    }

    /**
     * @return list<MonitoringLogServiceCheck>
     */
    public function listByMonitoringLogId(int $monitoringLogId): array
    {
        $rows = $this->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM monitoring_log_service_checks WHERE monitoring_log_id = ? ORDER BY id',
            [$monitoringLogId]
        );

        return array_map(fn (array $row) => $this->mapRowToMonitoringLogServiceCheck($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToMonitoringLogServiceCheck(array $row): MonitoringLogServiceCheck
    {
        return new MonitoringLogServiceCheck(
            isset($row['id']) ? (int) $row['id'] : null,
            isset($row['monitoring_log_id']) ? (int) $row['monitoring_log_id'] : null,
            isset($row['service_check_id']) ? (int) $row['service_check_id'] : null,
            isset($row['is_running']) ? (bool) $row['is_running'] : false,
            $row['output_message'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
