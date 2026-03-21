<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Tests\DatabaseTestCase;

final class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository($this->pdo);
    }

    public function testCreateAndFindById(): void
    {
        $user = new User(null, 'Test User', 'test@example.com', 'hashed');

        $id = $this->repository->create($user);

        $this->assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id, $found->getId());
        $this->assertSame('Test User', $found->getName());
        $this->assertSame('test@example.com', $found->getEmail());
    }

    public function testFindByEmail(): void
    {
        $this->pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('Admin', 'admin@test.com', 'hash', datetime('now'), datetime('now'))");

        $found = $this->repository->findByEmail('admin@test.com');

        $this->assertNotNull($found);
        $this->assertSame('admin@test.com', $found->getEmail());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($found);
    }

    public function testList(): void
    {
        $users = $this->repository->list();

        $this->assertIsArray($users);
        $this->assertGreaterThanOrEqual(0, count($users));
    }
}
