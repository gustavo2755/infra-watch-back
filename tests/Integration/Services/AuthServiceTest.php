<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\AuthServiceInterface;
use App\Exceptions\HttpException;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Tests\DatabaseTestCase;

final class AuthServiceTest extends DatabaseTestCase
{
    private AuthServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService(new UserRepository($this->pdo));
    }

    public function testAuthenticateValid(): void
    {
        $hash = password_hash('secret123', PASSWORD_DEFAULT);

        $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('Test', 'auth@test.com', '$hash', datetime('now'), datetime('now'))");

        $user = $this->service->authenticate(['email' => 'auth@test.com', 'password' => 'secret123']);

        $this->assertNotNull($user);
        $this->assertSame('auth@test.com', $user->getEmail());
        $this->assertSame('Test', $user->getName());
    }

    public function testEmailNotFound(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->service->authenticate(['email' => 'nonexistent@test.com', 'password' => 'any']);
    }

    public function testInvalidPassword(): void
    {
        $hash = password_hash('correct', PASSWORD_DEFAULT);

        $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('U', 'pwd@test.com', '$hash', datetime('now'), datetime('now'))");

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->service->authenticate(['email' => 'pwd@test.com', 'password' => 'wrong']);
    }

    public function testExceptionCorrectWhenLoginFails(): void
    {
        try {
            $this->service->authenticate(['email' => 'nope@test.com', 'password' => 'x']);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
            $this->assertSame('Invalid credentials', $e->getMessage());
        }
    }
}
