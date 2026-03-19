<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDOException;

/**
 * Repository for user persistence and queries.
 */
final class UserRepository extends BaseRepository
{
    public function findById(int $id): ?User
    {
        $row = $this->fetchOne('SELECT id, name, email, password, created_at, updated_at FROM users WHERE id = ?', [$id]);
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne('SELECT id, name, email, password, created_at, updated_at FROM users WHERE email = ?', [$email]);
        return $row ? $this->mapRowToUser($row) : null;
    }

    /**
     * @throws PDOException
     */
    public function create(User $user): int
    {
        $now = $this->now();
        $this->execute(
            "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, $now, $now)",
            [$user->getName(), $user->getEmail(), $user->getPassword()]
        );
        return $this->lastInsertId();
    }

    /**
     * @return list<User>
     */
    public function list(): array
    {
        $rows = $this->fetchAll('SELECT id, name, email, password, created_at, updated_at FROM users ORDER BY id');
        return array_map(fn (array $r) => $this->mapRowToUser($r), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToUser(array $row): User
    {
        return new User(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['name'] ?? null,
            $row['email'] ?? null,
            $row['password'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
