<?php

declare(strict_types=1);

namespace Tests;

use Database\Migrator;
use Database\Seeders\DatabaseSeeder;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA foreign_keys = ON');

        $migrator = new Migrator($this->pdo, __DIR__ . '/../database/migrations');
        $migrator->fresh();
        $migrator->run();

        $seeder = new DatabaseSeeder($this->pdo);
        $seeder->run();
    }
}
