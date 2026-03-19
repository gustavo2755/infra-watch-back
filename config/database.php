<?php

declare(strict_types=1);

return [
    'connection' => $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'mysql',
    'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'mysql',
    'port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306),
    'database' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '',
    'username' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '',
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
];
