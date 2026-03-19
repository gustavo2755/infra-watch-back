<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $fk = $driver === 'sqlite'
        ? 'FOREIGN KEY (server_id) REFERENCES servers(id), FOREIGN KEY (service_check_id) REFERENCES service_checks(id)'
        : 'FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE, FOREIGN KEY (service_check_id) REFERENCES service_checks(id) ON DELETE CASCADE';
    $pk = $driver === 'sqlite'
        ? 'PRIMARY KEY (server_id, service_check_id)'
        : 'PRIMARY KEY (server_id, service_check_id)';
    $ts = $driver === 'sqlite' ? 'created_at DATETIME, updated_at DATETIME' : 'created_at DATETIME NULL, updated_at DATETIME NULL';
    $sql = "CREATE TABLE server_service_checks (server_id INT NOT NULL, service_check_id INT NOT NULL, $ts, $pk, $fk)";
    $pdo->exec($sql);
};
