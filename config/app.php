<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Infra Watch',
    'env' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production',
    'url' => $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8000',
];
