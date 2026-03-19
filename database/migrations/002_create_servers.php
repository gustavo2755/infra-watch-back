<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $idCol = $driver === 'sqlite' ? 'id INTEGER PRIMARY KEY AUTOINCREMENT' : 'id INT AUTO_INCREMENT PRIMARY KEY';
    $fk = $driver === 'sqlite'
        ? 'FOREIGN KEY (created_by) REFERENCES users(id)'
        : 'FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL';
    $sql = "CREATE TABLE servers (
        $idCol,
        name VARCHAR(255),
        description TEXT,
        ip_address VARCHAR(45),
        is_active TINYINT(1) DEFAULT 1,
        monitor_resources TINYINT(1) DEFAULT 1,
        cpu_total DECIMAL(10,2),
        ram_total DECIMAL(10,2),
        disk_total DECIMAL(10,2),
        check_interval_seconds INT,
        last_check_at DATETIME,
        retention_days INT,
        cpu_alert_threshold DECIMAL(5,2),
        ram_alert_threshold DECIMAL(5,2),
        disk_alert_threshold DECIMAL(5,2),
        bandwidth_alert_threshold DECIMAL(5,2),
        alert_cpu_enabled TINYINT(1) DEFAULT 1,
        alert_ram_enabled TINYINT(1) DEFAULT 1,
        alert_disk_enabled TINYINT(1) DEFAULT 1,
        alert_bandwidth_enabled TINYINT(1) DEFAULT 1,
        created_by INT,
        created_at DATETIME,
        updated_at DATETIME,
        $fk
    )";
    $pdo->exec($sql);
    $pdo->exec('CREATE INDEX idx_servers_name ON servers(name)');
    $pdo->exec('CREATE INDEX idx_servers_is_active ON servers(is_active)');
};
