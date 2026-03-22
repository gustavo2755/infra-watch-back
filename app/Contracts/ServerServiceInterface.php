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

    public function delete(int $id): void;

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

    /**
     * @return array{items: list<Server>, total: int}
     */
    public function listPaginated(int $page, int $perPage): array;

    /**
     * @return array{items: list<Server>, total: int}
     */
    public function filterByNamePaginated(string $name, int $page, int $perPage): array;

    /**
     * @return array{items: list<Server>, total: int}
     */
    public function filterByIsActivePaginated(bool $isActive, int $page, int $perPage): array;
}
