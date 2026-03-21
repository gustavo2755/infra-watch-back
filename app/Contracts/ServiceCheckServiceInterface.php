<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\ServiceCheck;

interface ServiceCheckServiceInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): ServiceCheck;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ServiceCheck;

    public function findById(int $id): ServiceCheck;

    public function findBySlug(string $slug): ServiceCheck;

    /**
     * @return list<ServiceCheck>
     */
    public function list(): array;

    public function attachToServer(int $serverId, int $serviceCheckId): void;

    public function detachFromServer(int $serverId, int $serviceCheckId): void;

    public function delete(int $id): void;

    /**
     * @return list<ServiceCheck>
     */
    public function listByServerId(int $serverId): array;

    /**
     * @return list<ServiceCheck>
     */
    public function listAvailableByServerId(int $serverId): array;
}
