<?php

declare(strict_types=1);

$config = require __DIR__ . '/../bootstrap/app.php';
$appName = $config['app']['name'] ?? 'Infra Watch';
$appEnv = $config['app']['env'] ?? 'production';

header('Content-Type: text/plain; charset=UTF-8');
echo sprintf("%s is running in %s environment.\n", $appName, $appEnv);
