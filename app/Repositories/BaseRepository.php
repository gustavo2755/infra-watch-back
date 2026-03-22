<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

/**
 * Base repository providing common database access utilities.
 */
abstract class BaseRepository
{
    public function __construct(
        protected PDO $pdo
    ) {
    }

    protected function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? "datetime('now')"
            : 'NOW()';
    }

    /**
     * @param array<int, mixed> $params
     * @throws PDOException
     */
    protected function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $index => $value) {
            $position = $index + 1;
            if (is_int($value)) {
                $stmt->bindValue($position, $value, PDO::PARAM_INT);
                continue;
            }

            if (is_bool($value)) {
                $stmt->bindValue($position, $value, PDO::PARAM_BOOL);
                continue;
            }

            if ($value === null) {
                $stmt->bindValue($position, null, PDO::PARAM_NULL);
                continue;
            }

            $stmt->bindValue($position, (string) $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     * @param array<int, mixed> $params
     * @return array<string, mixed>|false
     */
    protected function fetchOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->execute($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: false;
    }

    /**
     * @param array<int, mixed> $params
     * @return list<array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows !== false ? $rows : [];
    }

    protected function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
