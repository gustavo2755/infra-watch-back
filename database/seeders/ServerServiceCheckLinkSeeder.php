<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class ServerServiceCheckLinkSeeder
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function run(): void
    {
        $serverIds = $this->seedServerIds();
        if ($serverIds === []) {
            return;
        }

        $slugToId = $this->serviceCheckIdsBySlug();
        if ($slugToId === []) {
            return;
        }

        $plans = [
            ['nginx', 'mysql'],
            ['nginx', 'apache2', 'php-fpm'],
            ['mysql', 'redis'],
            ['nginx', 'postgresql'],
            ['apache2', 'php-fpm', 'memcached'],
            ['nginx', 'mysql', 'redis', 'php-fpm'],
            ['mongodb', 'redis'],
            ['nginx', 'elasticsearch'],
            ['rabbitmq', 'redis'],
            ['nginx', 'mysql', 'certbot'],
            ['prometheus', 'nginx'],
            ['apache2', 'mysql', 'postgresql'],
        ];

        $now = $this->now();
        $existsStmt = $this->pdo->prepare(
            'SELECT 1 FROM server_service_checks WHERE server_id = ? AND service_check_id = ? AND deleted_at IS NULL'
        );
        $insertStmt = $this->pdo->prepare(
            "INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at, deleted_at) VALUES (?, ?, $now, $now, NULL)"
        );

        foreach ($serverIds as $index => $serverId) {
            $slugs = $plans[$index] ?? ['nginx'];
            foreach ($slugs as $slug) {
                $serviceCheckId = $slugToId[$slug] ?? null;
                if ($serviceCheckId === null) {
                    continue;
                }
                $existsStmt->execute([$serverId, $serviceCheckId]);
                if ($existsStmt->fetch()) {
                    continue;
                }
                $insertStmt->execute([$serverId, $serviceCheckId]);
            }
        }
    }

    private function seedServerIds(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id FROM servers WHERE name LIKE 'Seed Server %' AND deleted_at IS NULL ORDER BY name ASC"
        );
        if ($stmt === false) {
            return [];
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $r): int => (int) $r['id'], $rows);
    }

    private function serviceCheckIdsBySlug(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, slug FROM service_checks WHERE deleted_at IS NULL ORDER BY id ASC'
        );
        if ($stmt === false) {
            return [];
        }
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[(string) $row['slug']] = (int) $row['id'];
        }

        return $map;
    }

    private function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'datetime("now")' : 'NOW()';
    }
}
