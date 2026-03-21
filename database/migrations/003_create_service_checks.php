<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $sql = $driver === 'sqlite'
        ? 'CREATE TABLE IF NOT EXISTS service_checks (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL, slug VARCHAR(255) UNIQUE NOT NULL, description TEXT, created_at DATETIME, updated_at DATETIME)'
        : 'CREATE TABLE IF NOT EXISTS service_checks (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, slug VARCHAR(255) UNIQUE NOT NULL, description TEXT, created_at DATETIME NULL, updated_at DATETIME NULL)';
    $pdo->exec($sql);
};
