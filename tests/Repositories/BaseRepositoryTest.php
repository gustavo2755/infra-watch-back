<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Repositories\BaseRepository;
use Tests\DatabaseTestCase;

final class BaseRepositoryTest extends DatabaseTestCase
{
    public function testConnectionIsValid(): void
    {
        $repo = new class($this->pdo) extends BaseRepository {
        };

        $result = $this->pdo->query('SELECT 1')->fetchColumn();

        $this->assertSame(1, (int) $result);
    }

    public function testExecuteThrowsOnInvalidSql(): void
    {
        $this->expectException(\PDOException::class);

        $repo = new class($this->pdo) extends BaseRepository {
            public function runInvalid(): void
            {
                $this->execute('SELECT * FROM nonexistent_table_xyz_123');
            }
        };

        $repo->runInvalid();
    }
}
