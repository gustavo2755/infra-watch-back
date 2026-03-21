<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$driver = $_ENV['DB_CONNECTION'] ?? 'mysql';
$host = $_ENV['DB_HOST'] ?? 'mysql';
$port = $_ENV['DB_PORT'] ?? 3306;
$database = $_ENV['DB_DATABASE'] ?? 'infra_watch';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';

if ($driver === 'sqlite') {
    $path = $database === ':memory:' ? ':memory:' : (__DIR__ . '/../' . $database);
    $dsn = 'sqlite:' . $path;
    $pdo = new PDO($dsn);
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$migrator = new Database\Migrator($pdo, __DIR__ . '/migrations');

$fresh = ($argv[1] ?? '') === 'fresh';
if ($fresh) {
    $migrator->fresh();
    echo "Database dropped.\n";
}

$migrator->run();

echo "Migrations completed.\n";
