<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $idCol = $driver === 'sqlite' ? 'id INTEGER PRIMARY KEY AUTOINCREMENT' : 'id INT AUTO_INCREMENT PRIMARY KEY';
    $fk = $driver === 'sqlite'
        ? 'FOREIGN KEY (server_id) REFERENCES servers(id)'
        : 'FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE';
    $nullableDateTime = $driver === 'sqlite' ? 'DATETIME' : 'DATETIME NULL';

    $sql = "CREATE TABLE IF NOT EXISTS monitoring_logs (
        $idCol,
        server_id INT NOT NULL,
        checked_at DATETIME,
        is_up TINYINT(1) DEFAULT 0,
        cpu_usage_percent DECIMAL(5,2),
        ram_usage_percent DECIMAL(5,2),
        disk_usage_percent DECIMAL(5,2),
        bandwidth_usage_percent DECIMAL(5,2),
        is_alert TINYINT(1) DEFAULT 0,
        alert_type VARCHAR(255),
        error_message TEXT,
        sent_to_email VARCHAR(255),
        created_at $nullableDateTime,
        updated_at $nullableDateTime,
        $fk
    )";

    $pdo->exec($sql);

    $indexes = [
        'idx_monitoring_logs_server_id' => 'CREATE INDEX idx_monitoring_logs_server_id ON monitoring_logs(server_id)',
        'idx_monitoring_logs_checked_at' => 'CREATE INDEX idx_monitoring_logs_checked_at ON monitoring_logs(checked_at)',
        'idx_monitoring_logs_is_alert' => 'CREATE INDEX idx_monitoring_logs_is_alert ON monitoring_logs(is_alert)',
        'idx_monitoring_logs_server_checked_at' => 'CREATE INDEX idx_monitoring_logs_server_checked_at ON monitoring_logs(server_id, checked_at)',
    ];

    foreach ($indexes as $name => $createSql) {
        $exists = $driver === 'sqlite'
            ? (bool) $pdo->query("SELECT 1 FROM sqlite_master WHERE type='index' AND name='$name'")->fetch()
            : (bool) $pdo->query("SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'monitoring_logs' AND index_name = '$name'")->fetch();

        if (!$exists) {
            $pdo->exec($createSql);
        }
    }
};
