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
        $sc = new ServiceCheck(null, 'Redis Test', 'test-redis-repo', 'Cache server');

        $id = $this->repository->create($sc);

        $this->assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id, $found->getId());
        $this->assertSame('test-redis-repo', $found->getSlug());
    }

    public function testFindBySlug(): void
    {
        $found = $this->repository->findBySlug('nginx');

        $this->assertNotNull($found);
        $this->assertSame('nginx', $found->getSlug());
    }

    public function testUpdate(): void
    {
        $sc = new ServiceCheck(null, 'Redis Test', 'test-redis-repo-upd', 'Cache');

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

    public function testDeleteSoftDeletesRecord(): void
    {
        $sc = new ServiceCheck(null, 'ToSoftDelete', 'to-soft-delete', null);
        $id = $this->repository->create($sc);

        $this->repository->delete($id);

        $this->assertNull($this->repository->findById($id));

        $row = $this->pdo->query("SELECT id, deleted_at FROM service_checks WHERE id = $id")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);
        $this->assertNotNull($row['deleted_at']);
    }

    public function testListExcludesSoftDeleted(): void
    {
        $sc1 = new ServiceCheck(null, 'Keep', 'keep-sc', null);
        $sc2 = new ServiceCheck(null, 'DeleteMe', 'delete-me-sc', null);
        $id1 = $this->repository->create($sc1);
        $id2 = $this->repository->create($sc2);

        $this->repository->delete($id2);

        $list = $this->repository->list();
        $ids = array_map(fn (ServiceCheck $s) => $s->getId(), $list);
        $this->assertContains($id1, $ids);
        $this->assertNotContains($id2, $ids);
    }

    public function testListPaginatedAndCount(): void
    {
        $this->repository->create(new ServiceCheck(null, 'Paginated A', 'paginated-a', null));
        $this->repository->create(new ServiceCheck(null, 'Paginated B', 'paginated-b', null));
        $id = $this->repository->create(new ServiceCheck(null, 'Paginated C', 'paginated-c', null));
        $this->repository->delete($id);

        $page = $this->repository->listPaginated(1, 2);
        $total = $this->repository->countAll();

        $this->assertCount(2, $page);
        $this->assertGreaterThanOrEqual(2, $total);
        foreach ($page as $item) {
            $this->assertNotSame($id, $item->getId());
        }
    }
}
