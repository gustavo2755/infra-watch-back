<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $name = 'idx_servers_last_check_at';
    $sql = 'CREATE INDEX idx_servers_last_check_at ON servers(last_check_at)';
    $exists = $driver === 'sqlite'
        ? (bool) $pdo->query("SELECT 1 FROM sqlite_master WHERE type='index' AND name='$name'")->fetch()
        : (bool) $pdo->query("SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'servers' AND index_name = '$name'")->fetch();

    if (!$exists) {
        $pdo->exec($sql);
    }
};
