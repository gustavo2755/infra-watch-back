<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Simple session wrapper for authentication.
 */
final class Session
{
    public function setUserId(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
    }

    public function getUserId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    public function clear(): void
    {
        $_SESSION = [];
    }
}
