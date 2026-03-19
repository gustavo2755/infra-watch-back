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
        $stmt = $this->pdo->prepare("INSERT INTO service_checks (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, $now, $now)");

        foreach ($checks as $check) {
            $stmt->execute($check);
        }
    }

    private function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'datetime("now")' : 'NOW()';
    }
}
