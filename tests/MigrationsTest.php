<?php

declare(strict_types=1);

namespace Tests;

use PDO;

final class MigrationsTest extends DatabaseTestCase
{
    public function testTablesAreCreated(): void
    {
        $tables = ['users', 'servers', 'service_checks', 'server_service_checks'];

        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            $row = $stmt->fetch();
            $this->assertNotFalse($row, "Table $table should exist");
        }
    }

    public function testUsersTableHasExpectedColumns(): void
    {
        $cols = $this->getColumns('users');
        $expected = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];

        foreach ($expected as $col) {
            $this->assertContains($col, $cols, "users should have column $col");
        }
    }

    public function testServersTableHasExpectedColumns(): void
    {
        $cols = $this->getColumns('servers');

        $expected = [
            'id', 'name', 'description', 'ip_address', 'is_active', 'monitor_resources',
            'cpu_total', 'ram_total', 'disk_total', 'check_interval_seconds', 'last_check_at',
            'retention_days', 'cpu_alert_threshold', 'ram_alert_threshold', 'disk_alert_threshold',
            'bandwidth_alert_threshold', 'alert_cpu_enabled', 'alert_ram_enabled', 'alert_disk_enabled',
            'alert_bandwidth_enabled', 'created_by', 'created_at', 'updated_at'
        ];

        foreach ($expected as $col) {
            $this->assertContains($col, $cols, "servers should have column $col");
        }
    }

    public function testServersTableHasIndexesOnNameAndIsActive(): void
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='servers' AND name LIKE 'idx_servers_%'");
        $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains('idx_servers_name', $indexes);
        $this->assertContains('idx_servers_is_active', $indexes);
    }

    public function testServiceChecksTableHasSlugNotCheckCommand(): void
    {
        $cols = $this->getColumns('service_checks');

        $this->assertContains('slug', $cols);
        $this->assertNotContains('check_command', $cols);
    }

    public function testServiceChecksTableHasExpectedColumns(): void
    {
        $cols = $this->getColumns('service_checks');

        $expected = ['id', 'name', 'slug', 'description', 'created_at', 'updated_at'];

        foreach ($expected as $col) {
            $this->assertContains($col, $cols, "service_checks should have column $col");
        }
    }

    public function testServerServiceChecksHasCompositePrimaryKey(): void
    {
        $pk = $this->getPrimaryKey('server_service_checks');

        $this->assertNotEmpty($pk);
        $this->assertContains('server_id', $pk);
        $this->assertContains('service_check_id', $pk);
    }

    public function testServerServiceChecksHasTimestamps(): void
    {
        $cols = $this->getColumns('server_service_checks');

        $this->assertContains('created_at', $cols);
        $this->assertContains('updated_at', $cols);
    }

    public function testSeedServiceChecksWithSlugs(): void
    {
        $stmt = $this->pdo->query('SELECT slug FROM service_checks ORDER BY slug');
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains('nginx', $slugs);
        $this->assertContains('mysql', $slugs);
        $this->assertContains('apache2', $slugs);
        $this->assertContains('php-fpm', $slugs);
    }

    public function testServerServiceChecksRelationshipIntegrity(): void
    {
        $this->pdo->exec('INSERT INTO servers (name, ip_address, created_at, updated_at) VALUES ("test", "127.0.0.1", datetime("now"), datetime("now"))');
        $this->pdo->exec('INSERT INTO server_service_checks (server_id, service_check_id, created_at, updated_at) VALUES (1, 1, datetime("now"), datetime("now"))');

        $stmt = $this->pdo->query('SELECT 1 FROM server_service_checks WHERE server_id = 1 AND service_check_id = 1');

        $this->assertNotFalse($stmt->fetch());
    }

    public function testUniqueIndexOnUsersEmail(): void
    {
        $this->pdo->exec('INSERT INTO users (name, email, created_at, updated_at) VALUES ("a", "a@b.com", datetime("now"), datetime("now"))');
        $this->expectException(\PDOException::class);
        $this->pdo->exec('INSERT INTO users (name, email, created_at, updated_at) VALUES ("b", "a@b.com", datetime("now"), datetime("now"))');
    }

    public function testUniqueIndexOnServiceChecksSlug(): void
    {
        $this->expectException(\PDOException::class);
        $this->pdo->exec('INSERT INTO service_checks (name, slug, created_at, updated_at) VALUES ("dup", "nginx", datetime("now"), datetime("now"))');
    }

    /**
     * @return list<string>
     */
    private function getColumns(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info($table)");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, 'name');
    }

    /**
     * @return list<string>
     */
    private function getPrimaryKey(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info($table)");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pk = [];

        foreach ($rows as $row) {
            if ((int) $row['pk'] > 0) {
                $pk[] = $row['name'];
            }
        }

        return $pk;
    }
}
