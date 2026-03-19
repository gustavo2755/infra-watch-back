<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

final class DatabaseSeeder
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function run(): void
    {
        (new UserSeeder($this->pdo))->run();
        (new ServiceCheckSeeder($this->pdo))->run();
    }
}
