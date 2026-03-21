<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $tables = ['servers', 'service_checks', 'server_service_checks'];

    foreach ($tables as $table) {
        $cols = $driver === 'sqlite'
            ? array_column($pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC), 'name')
            : array_column($pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC), 'Field');

        if (!in_array('deleted_at', $cols, true)) {
            $type = $driver === 'sqlite' ? 'DATETIME' : 'DATETIME NULL';
            $pdo->exec("ALTER TABLE $table ADD COLUMN deleted_at $type");
        }
    }
};
