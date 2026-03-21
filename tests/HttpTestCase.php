<?php

declare(strict_types=1);

namespace Tests;

use App\Container;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Resources\ErrorResource;
use Database\Migrator;
use Database\Seeders\DatabaseSeeder;
use PHPUnit\Framework\TestCase;

abstract class HttpTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['JWT_SECRET'] = 'test-secret-at-least-32-chars-for-hs256';
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');

        $migrator = new Migrator($pdo, __DIR__ . '/../database/migrations');
        $migrator->fresh();
        $migrator->run();

        $seeder = new DatabaseSeeder($pdo);
        $seeder->run();

        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('Test User', 'test@example.com', ?, datetime('now'), datetime('now'))");
        $stmt->execute([$hash]);

        $this->container = new Container([], $pdo);
    }

    /**
     * @return array{statusCode: int, body: array<string, mixed>}
     */
    protected function request(
        string $method,
        string $path,
        array $body = [],
        array $query = [],
        ?array $headers = null
    ): array {
        $request = new Request(
            $method,
            $path,
            $query,
            $body,
            [],
            $headers ?? []
        );
        $routes = (require __DIR__ . '/../routes/api.php')($this->container);
        $router = new Router($routes);

        ob_start();

        try {
            $router->dispatch($request);
        } catch (\App\Exceptions\ValidationException $e) {
            Response::json(ErrorResource::fromValidationException($e), 422);
        } catch (\App\Exceptions\HttpException $e) {
            Response::json(ErrorResource::fromHttpException($e), $e->getStatusCode());
        } catch (\Throwable $e) {
            Response::json(ErrorResource::fromThrowable($e), 500);
        }

        $output = ob_get_clean();
        $decoded = json_decode($output ?: '{}', true);
        $statusCode = http_response_code() ?: 200;

        return [
            'statusCode' => $statusCode,
            'body' => is_array($decoded) ? $decoded : [],
        ];
    }
}
