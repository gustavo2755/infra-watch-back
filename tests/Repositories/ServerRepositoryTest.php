<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Models\Server;
use App\Repositories\ServerRepository;
use Tests\DatabaseTestCase;

final class ServerRepositoryTest extends DatabaseTestCase
{
    private ServerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ServerRepository($this->pdo);
    }

    public function testCreateAndFindById(): void
    {
        $server = new Server(null, 'Web Server', 'Main web', '192.168.1.1');

        $id = $this->repository->create($server);

        $this->assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id, $found->getId());
        $this->assertSame('Web Server', $found->getName());
        $this->assertSame('192.168.1.1', $found->getIpAddress());
    }

    public function testUpdate(): void
    {
        $server = new Server(null, 'Original', null, '10.0.0.1');

        $id = $this->repository->create($server);
        $server->setId($id);
        $server->setName('Updated Name');
        $server->setIpAddress('10.0.0.2');

        $this->repository->update($server);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame('Updated Name', $found->getName());
        $this->assertSame('10.0.0.2', $found->getIpAddress());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function testList(): void
    {
        $servers = $this->repository->list();

        $this->assertIsArray($servers);
    }

    public function testFilterByName(): void
    {
        $this->repository->create(new Server(null, 'Alpha Server', null, '1.1.1.1'));
        $this->repository->create(new Server(null, 'Beta Server', null, '2.2.2.2'));
        $this->repository->create(new Server(null, 'Alpha Backup', null, '3.3.3.3'));

        $results = $this->repository->filterByName('Alpha');

        $this->assertCount(2, $results);

        $names = array_map(fn (Server $s) => $s->getName(), $results);

        $this->assertContains('Alpha Server', $names);
        $this->assertContains('Alpha Backup', $names);
    }

    public function testFilterByIsActive(): void
    {
        $s1 = new Server(null, 'Active', null, '1.1.1.1');
        $s1->setIsActive(true);
        $s2 = new Server(null, 'Inactive', null, '2.2.2.2');
        $s2->setIsActive(false);

        $this->repository->create($s1);
        $this->repository->create($s2);

        $active = $this->repository->filterByIsActive(true);
        $inactive = $this->repository->filterByIsActive(false);

        $this->assertGreaterThanOrEqual(1, count($active));
        $this->assertGreaterThanOrEqual(1, count($inactive));
    }

    public function testCreateFailsWhenForeignKeyViolated(): void
    {
        $this->expectException(\PDOException::class);

        $server = new Server(null, 'Test', null, '1.1.1.1');
        $server->setCreatedBy(99999);

        $this->repository->create($server);
    }
}
