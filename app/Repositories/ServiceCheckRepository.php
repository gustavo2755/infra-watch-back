<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ServiceCheck;
use PDOException;

/**
 * Repository for service check persistence and queries.
 */
final class ServiceCheckRepository extends BaseRepository
{
    public function findById(int $id): ?ServiceCheck
    {
        $row = $this->fetchOne('SELECT id, name, slug, description, created_at, updated_at, deleted_at FROM service_checks WHERE id = ? AND deleted_at IS NULL', [$id]);

        return $row ? $this->mapRowToServiceCheck($row) : null;
    }

    public function findBySlug(string $slug): ?ServiceCheck
    {
        $row = $this->fetchOne('SELECT id, name, slug, description, created_at, updated_at, deleted_at FROM service_checks WHERE slug = ? AND deleted_at IS NULL', [$slug]);

        return $row ? $this->mapRowToServiceCheck($row) : null;
    }

    /**
     * @throws PDOException
     */
    public function create(ServiceCheck $serviceCheck): int
    {
        $now = $this->now();
        $this->execute(
            "INSERT INTO service_checks (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, $now, $now)",
            [$serviceCheck->getName(), $serviceCheck->getSlug(), $serviceCheck->getDescription()]
        );

        return $this->lastInsertId();
    }

    /**
     * @throws PDOException
     */
    public function update(ServiceCheck $serviceCheck): int
    {
        $now = $this->now();
        $this->execute(
            "UPDATE service_checks SET name = ?, slug = ?, description = ?, updated_at = $now WHERE id = ?",
            [$serviceCheck->getName(), $serviceCheck->getSlug(), $serviceCheck->getDescription(), $serviceCheck->getId()]
        );

        return (int) $serviceCheck->getId();
    }

    /**
     * @throws PDOException
     */
    public function delete(int $id): void
    {
        $now = $this->now();
        $this->execute("UPDATE service_checks SET deleted_at = $now WHERE id = ?", [$id]);
    }

    /**
     * @return list<ServiceCheck>
     */
    public function list(): array
    {
        $rows = $this->fetchAll('SELECT id, name, slug, description, created_at, updated_at, deleted_at FROM service_checks WHERE deleted_at IS NULL ORDER BY id');

        return array_map(fn (array $r) => $this->mapRowToServiceCheck($r), $rows);
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
            $row['updated_at'] ?? null,
            $row['deleted_at'] ?? null
        );
    }
}
