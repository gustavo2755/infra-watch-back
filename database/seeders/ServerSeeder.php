<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class ServerSeeder
{
    public function __construct(private PDO $pdo)
    {
    }

    public function run(): void
    {
        $userId = $this->resolveCreatedByUserId();
        if ($userId === null) {
            return;
        }

        $now = $this->now();
        $existsStmt = $this->pdo->prepare('SELECT 1 FROM servers WHERE name = ? AND deleted_at IS NULL');
        $insertSql = 'INSERT INTO servers ('
            . 'name, description, ip_address, is_active, monitor_resources, '
            . 'cpu_total, ram_total, disk_total, check_interval_seconds, last_check_at, retention_days, '
            . 'cpu_alert_threshold, ram_alert_threshold, disk_alert_threshold, bandwidth_alert_threshold, '
            . 'alert_cpu_enabled, alert_ram_enabled, alert_disk_enabled, alert_bandwidth_enabled, '
            . 'created_by, created_at, updated_at, deleted_at'
            . ') VALUES ('
            . '?, ?, ?, ?, ?, '
            . '?, ?, ?, ?, ' . $now . ', ?, '
            . '?, ?, ?, ?, '
            . '1, 1, 1, 1, '
            . '?, ' . $now . ', ' . $now . ', NULL'
            . ')';
        $insertStmt = $this->pdo->prepare($insertSql);

        for ($i = 1; $i <= 12; $i++) {
            $name = sprintf('Seed Server %02d', $i);
            $existsStmt->execute([$name]);
            if ($existsStmt->fetch()) {
                continue;
            }

            $monitorResources = $i % 2 === 1;
            $insertStmt->execute([
                $name,
                'Seeded demo server for local testing',
                sprintf('10.20.0.%d', $i),
                1,
                $monitorResources ? 1 : 0,
                4.0,
                8.0,
                100.0,
                60,
                30,
                90.0,
                90.0,
                90.0,
                100.0,
                $userId,
            ]);
        }
    }

    private function resolveCreatedByUserId(): ?int
    {
        $email = $_ENV['SEED_ADMIN_EMAIL'] ?? getenv('SEED_ADMIN_EMAIL') ?: 'admin@admin.com';
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? (int) $row['id'] : null;
    }

    private function now(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'datetime("now")' : 'NOW()';
    }
}
