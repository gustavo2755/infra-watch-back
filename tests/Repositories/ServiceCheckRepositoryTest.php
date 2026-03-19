<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Models\ServiceCheck;
use App\Repositories\ServiceCheckRepository;
use Tests\DatabaseTestCase;

final class ServiceCheckRepositoryTest extends DatabaseTestCase
{
    private ServiceCheckRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ServiceCheckRepository($this->pdo);
    }

    public function testCreateAndFindById(): void
    {
        $sc = new ServiceCheck(null, 'Redis', 'redis', 'Cache server');

        $id = $this->repository->create($sc);

        $this->assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id, $found->getId());
        $this->assertSame('redis', $found->getSlug());
    }

    public function testFindBySlug(): void
    {
        $found = $this->repository->findBySlug('nginx');

        $this->assertNotNull($found);
        $this->assertSame('nginx', $found->getSlug());
    }

    public function testUpdate(): void
    {
        $sc = new ServiceCheck(null, 'Redis', 'redis', 'Cache');

        $id = $this->repository->create($sc);
        $sc->setId($id);
        $sc->setDescription('Redis cache server');

        $this->repository->update($sc);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame('Redis cache server', $found->getDescription());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function testFindBySlugReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findBySlug('nonexistent-slug');

        $this->assertNull($found);
    }

    public function testList(): void
    {
        $list = $this->repository->list();

        $this->assertIsArray($list);
        $this->assertGreaterThanOrEqual(4, count($list));
    }
}
