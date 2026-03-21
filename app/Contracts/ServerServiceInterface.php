<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Server;

interface ServerServiceInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Server;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Server;

    public function findById(int $id): Server;

    /**
     * @return list<Server>
     */
    public function list(): array;

    /**
     * @return list<Server>
     */
    public function filterByName(string $name): array;

    /**
     * @return list<Server>
     */
    public function filterByIsActive(bool $isActive): array;
}
