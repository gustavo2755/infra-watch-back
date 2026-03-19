<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ServiceCheck;
use PDOException;

/**
 * Repository for server-service_check link persistence and queries.
 */
final class ServerServiceCheckRepository extends BaseRepository
{
    /**
     * @throws PDOException
     */
    public function attach(int $serverId, int $serviceCheckId): void
    {
        $now = $this->now();

        $this->execute(
            "INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES (?, ?, $now, $now)",
            [$serverId, $serviceCheckId]
        );
    }

    /**
     * @return list<ServiceCheck>
     */
    public function listByServerId(int $serverId): array
    {
        $sql = 'SELECT sc.id, sc.name, sc.slug, sc.description, sc.created_at, sc.updated_at
                FROM service_checks sc
                INNER JOIN server_service_checks ssc ON ssc.service_check_id = sc.id
                WHERE ssc.server_id = ?
                ORDER BY sc.id';
        $rows = $this->fetchAll($sql, [$serverId]);

        return array_map(fn (array $r) => $this->mapRowToServiceCheck($r), $rows);
    }

    public function exists(int $serverId, int $serviceCheckId): bool
    {
        $row = $this->fetchOne(
            'SELECT 1 FROM server_service_checks WHERE server_id = ? AND service_check_id = ?',
            [$serverId, $serviceCheckId]
        );

        return $row !== false;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToServiceCheck(array $row): ServiceCheck
    {
        return new ServiceCheck(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['name'] ?? null,
            $row['slug'] ?? null,
            $row['description'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
