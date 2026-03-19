<?php

declare(strict_types=1);

namespace Database;

use PDO;

final class Migrator
{
    public function __construct(
        private PDO $pdo,
        private string $migrationsPath
    ) {
    }

    public function run(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        }

        $this->createMigrationsTable();

        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.php');

            if ($this->isMigrated($name)) {
                continue;
            }

            $migration = require $file;

            if (is_callable($migration)) {
                $migration($this->pdo, $driver);
            }

            $this->markMigrated($name);
        }
    }

    public function fresh(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'")->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            $result = $this->pdo->query('SHOW TABLES');
            $tables = $result ? $result->fetchAll(PDO::FETCH_COLUMN) : [];
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS `$table`");
        }

        $this->pdo->exec('DROP TABLE IF EXISTS migrations');
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (name VARCHAR(255) PRIMARY KEY, run_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
    }

    private function isMigrated(string $name): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM migrations WHERE name = ?');

        $stmt->execute([$name]);

        return (bool) $stmt->fetch();
    }

    private function markMigrated(string $name): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO migrations (name) VALUES (?)');

        $stmt->execute([$name]);
    }
}
