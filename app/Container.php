<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AuthServiceInterface;
use App\Contracts\ServerServiceInterface;
use App\Contracts\ServiceCheckServiceInterface;
use App\Contracts\TokenServiceInterface;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Repositories\ServiceCheckRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\ServerService;
use App\Services\ServiceCheckService;
use App\Services\TokenService;
use PDO;

/**
 * Simple dependency container for the application.
 */
final class Container
{
    private ?PDO $pdo = null;
    private ?UserRepository $userRepository = null;
    private ?ServerRepository $serverRepository = null;
    private ?ServiceCheckRepository $serviceCheckRepository = null;
    private ?ServerServiceCheckRepository $serverServiceCheckRepository = null;
    private ?AuthService $authService = null;
    private ?ServerService $serverService = null;
    private ?ServiceCheckService $serviceCheckService = null;
    private ?TokenService $tokenService = null;

    public function __construct(
        private readonly array $config,
        ?PDO $pdo = null
    ) {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $db = $this->config['database'] ?? [];
            $driver = $db['connection'] ?? 'mysql';
            $host = $db['host'] ?? 'mysql';
            $port = $db['port'] ?? 3306;
            $database = $db['database'] ?? 'infra_watch';
            $username = $db['username'] ?? '';
            $password = $db['password'] ?? '';

            if ($driver === 'sqlite') {
                $path = $database === ':memory:' ? ':memory:' : (__DIR__ . '/../' . $database);
                $this->pdo = new PDO('sqlite:' . $path);
                $this->pdo->exec('PRAGMA foreign_keys = ON');
            } else {
                $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
                $this->pdo = new PDO($dsn, $username, $password);
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($driver !== 'sqlite') {
                $this->pdo->exec('SET NAMES utf8mb4');
            }
        }

        assert($this->pdo !== null);

        return $this->pdo;
    }

    public function getAuthService(): AuthServiceInterface
    {
        if ($this->authService === null) {
            $this->authService = new AuthService($this->getUserRepository());
        }

        return $this->authService;
    }

    public function getTokenService(): TokenServiceInterface
    {
        if ($this->tokenService === null) {
            $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'dev-secret-change-in-production-min-32-chars';
            $this->tokenService = new TokenService($secret);
        }

        return $this->tokenService;
    }

    public function getServerService(): ServerServiceInterface
    {
        if ($this->serverService === null) {
            $this->serverService = new ServerService($this->getServerRepository(), $this->getUserRepository());
        }

        return $this->serverService;
    }

    public function getServiceCheckService(): ServiceCheckServiceInterface
    {
        if ($this->serviceCheckService === null) {
            $this->serviceCheckService = new ServiceCheckService(
                $this->getServiceCheckRepository(),
                $this->getServerServiceCheckRepository(),
                $this->getServerRepository()
            );
        }

        return $this->serviceCheckService;
    }

    private function getUserRepository(): UserRepository
    {
        if ($this->userRepository === null) {
            $this->userRepository = new UserRepository($this->getPdo());
        }

        return $this->userRepository;
    }

    private function getServerRepository(): ServerRepository
    {
        if ($this->serverRepository === null) {
            $this->serverRepository = new ServerRepository($this->getPdo());
        }

        return $this->serverRepository;
    }

    private function getServiceCheckRepository(): ServiceCheckRepository
    {
        if ($this->serviceCheckRepository === null) {
            $this->serviceCheckRepository = new ServiceCheckRepository($this->getPdo());
        }

        return $this->serviceCheckRepository;
    }

    private function getServerServiceCheckRepository(): ServerServiceCheckRepository
    {
        if ($this->serverServiceCheckRepository === null) {
            $this->serverServiceCheckRepository = new ServerServiceCheckRepository($this->getPdo());
        }

        return $this->serverServiceCheckRepository;
    }
}
