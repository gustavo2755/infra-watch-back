<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class ServiceCheckSeeder
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function run(): void
    {
        $now = $this->now();
        $checks = [
            ['Nginx', 'nginx', 'Web server'],
            ['MySQL', 'mysql', 'Database server'],
            ['Apache', 'apache2', 'Web server'],
            ['PHP-FPM', 'php-fpm', 'PHP process manager'],
        ];

        $existsStmt = $this->pdo->prepare('SELECT 1 FROM service_checks WHERE slug = ?');
        $insertStmt = $this->pdo->prepare("INSERT INTO service_checks (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, $now, $now)");

        foreach ($checks as $check) {
            $existsStmt->execute([$check[1]]);
            if ($existsStmt->fetch()) {
                continue;
            }
            $insertStmt->execute($check);
        }
    }

    private function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'datetime("now")' : 'NOW()';
    }
}
