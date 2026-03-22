<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

/**
 * Seeds approximately ten monitoring logs per seed server (time-spaced for charts).
 */
final class MonitoringLogSeeder
{
    private const LOGS_PER_SERVER = 10;

    public function __construct(
        private PDO $pdo
    ) {
    }

    public function run(): void
    {
        $servers = $this->seedServersWithFlags();
        if ($servers === []) {
            return;
        }

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) AS c FROM monitoring_logs WHERE server_id = ?');
        $insertSql = 'INSERT INTO monitoring_logs (
            server_id, checked_at, is_up,
            cpu_usage_percent, ram_usage_percent, disk_usage_percent, bandwidth_usage_percent,
            is_alert, alert_type, error_message, sent_to_email,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )';
        $insertStmt = $this->pdo->prepare($insertSql);

        $base = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        foreach ($servers as $server) {
            $serverId = (int) $server['id'];
            $monitorResources = (int) $server['monitor_resources'] === 1;
            $countStmt->execute([$serverId]);
            $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
            $existing = (int) ($countRow['c'] ?? 0);

            if ($existing >= self::LOGS_PER_SERVER) {
                continue;
            }

            $toCreate = self::LOGS_PER_SERVER - $existing;

            for ($n = 0; $n < $toCreate; $n++) {
                $slot = $existing + $n;
                $minutesAgo = $slot * 5;
                $checkedAt = $base->modify(sprintf('-%d minutes', $minutesAgo))->format('Y-m-d H:i:s');

                $cpu = $monitorResources ? $this->randomMetric() : null;
                $ram = $monitorResources ? $this->randomMetric() : null;
                $disk = $monitorResources ? $this->randomMetric() : null;
                $bw = $monitorResources ? $this->randomMetric() : null;

                $isAlert = false;
                $alertType = null;
                $errorMessage = null;

                if ($monitorResources) {
                    $alert = self::alertForSlot($slot);
                    if ($alert !== null) {
                        $isAlert = true;
                        $alertType = $alert[0];
                        $errorMessage = $alert[1];
                    }
                }

                $insertStmt->execute([
                    $serverId,
                    $checkedAt,
                    1,
                    $cpu,
                    $ram,
                    $disk,
                    $bw,
                    $isAlert ? 1 : 0,
                    $alertType,
                    $errorMessage,
                    null,
                    $checkedAt,
                    $checkedAt,
                ]);
            }
        }
    }

    /**
     * @return list<array{id: int, monitor_resources: int}>
     */
    private function seedServersWithFlags(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, monitor_resources FROM servers WHERE name LIKE 'Seed Server %' AND deleted_at IS NULL ORDER BY name ASC"
        );

        if ($stmt === false) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $r): array => [
            'id' => (int) $r['id'],
            'monitor_resources' => (int) $r['monitor_resources'],
        ], $rows);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private static function alertForSlot(int $slot): ?array
    {
        return match ($slot) {
            2 => ['cpu', 'CPU threshold exceeded (seed)'],
            5 => ['ram', 'RAM threshold exceeded (seed)'],
            8 => ['disk', 'Disk threshold exceeded (seed)'],
            default => null,
        };
    }

    private function randomMetric(): float
    {
        return round(random_int(0, 5000) / 100, 2);
    }
}
