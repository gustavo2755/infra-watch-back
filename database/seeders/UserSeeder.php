<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class UserSeeder
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function run(): void
    {
        $email = $_ENV['SEED_ADMIN_EMAIL'] ?? 'admin@infra.watch';
        $password = $_ENV['SEED_ADMIN_PASSWORD'] ?? 'password123';

        if ($email === '' || $password === '') {
            return;
        }

        $check = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $check->execute([$email]);
        
        if ($check->fetch()) {
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = $this->now();
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, $now, $now)");
        $stmt->execute(['Admin', $email, $hash]);
    }

    private function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'datetime("now")' : 'NOW()';
    }
}
