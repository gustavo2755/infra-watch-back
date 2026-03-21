<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $cols = $driver === 'sqlite'
        ? array_column($pdo->query('PRAGMA table_info(server_service_checks)')->fetchAll(PDO::FETCH_ASSOC), 'name')
        : array_column($pdo->query("SHOW COLUMNS FROM server_service_checks")->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (!in_array('created_at', $cols, true)) {
        $type = $driver === 'sqlite' ? 'DATETIME' : 'DATETIME NULL';
        $pdo->exec("ALTER TABLE server_service_checks ADD COLUMN created_at $type");
    }
    if (!in_array('updated_at', $cols, true)) {
        $type = $driver === 'sqlite' ? 'DATETIME' : 'DATETIME NULL';
        $pdo->exec("ALTER TABLE server_service_checks ADD COLUMN updated_at $type");
    }
};
