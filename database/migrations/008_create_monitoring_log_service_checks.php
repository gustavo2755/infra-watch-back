<?php

declare(strict_types=1);

return function (PDO $pdo, string $driver): void {
    $idCol = $driver === 'sqlite' ? 'id INTEGER PRIMARY KEY AUTOINCREMENT' : 'id INT AUTO_INCREMENT PRIMARY KEY';
    $fk = $driver === 'sqlite'
        ? 'FOREIGN KEY (monitoring_log_id) REFERENCES monitoring_logs(id), FOREIGN KEY (service_check_id) REFERENCES service_checks(id)'
        : 'FOREIGN KEY (monitoring_log_id) REFERENCES monitoring_logs(id) ON DELETE CASCADE, FOREIGN KEY (service_check_id) REFERENCES service_checks(id) ON DELETE CASCADE';
    $nullableDateTime = $driver === 'sqlite' ? 'DATETIME' : 'DATETIME NULL';

    $sql = "CREATE TABLE IF NOT EXISTS monitoring_log_service_checks (
        $idCol,
        monitoring_log_id INT NOT NULL,
        service_check_id INT NOT NULL,
        is_running TINYINT(1) DEFAULT 0,
        output_message TEXT,
        created_at $nullableDateTime,
        updated_at $nullableDateTime,
        $fk
    )";

    $pdo->exec($sql);

    $indexName = 'idx_monitoring_log_service_checks_monitoring_log_id';
    $indexSql = 'CREATE INDEX idx_monitoring_log_service_checks_monitoring_log_id ON monitoring_log_service_checks(monitoring_log_id)';
    $exists = $driver === 'sqlite'
        ? (bool) $pdo->query("SELECT 1 FROM sqlite_master WHERE type='index' AND name='$indexName'")->fetch()
        : (bool) $pdo->query("SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'monitoring_log_service_checks' AND index_name = '$indexName'")->fetch();

    if (!$exists) {
        $pdo->exec($indexSql);
    }
};
