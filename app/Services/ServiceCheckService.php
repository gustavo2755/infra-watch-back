<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Models\ServiceCheck;
use App\Repositories\ServerRepository;
use App\Repositories\ServerServiceCheckRepository;
use App\Repositories\ServiceCheckRepository;

/**
 * Service for service check business logic.
 */
final class ServiceCheckService
{
    public function __construct(
        private ServiceCheckRepository $serviceCheckRepository,
        private ServerServiceCheckRepository $serverServiceCheckRepository,
        private ServerRepository $serverRepository
    ) {
    }

    /**
     * Creates a service check from validated payload.
     *
     * @param array{name: string, slug: string, description: string|null} $data Validated from StoreServiceCheckRequest
     */
    public function create(array $data): ServiceCheck
    {
        $serviceCheck = ServiceCheck::fromArray($data);

        $id = $this->serviceCheckRepository->create($serviceCheck);
        $serviceCheck->setId($id);

        return $serviceCheck;
    }

    /**
     * Updates a service check.
     *
     * @param array<string, mixed> $data name, slug, description
     * @throws HttpException 404 when service check not found
     */
    public function update(int $id, array $data): ServiceCheck
    {
        $serviceCheck = $this->serviceCheckRepository->findById($id);

        if ($serviceCheck === null) {
            throw new HttpException('Service check not found', 404);
        }

        $serviceCheck = $this->applyDataToServiceCheck($serviceCheck, $data);

        $this->serviceCheckRepository->update($serviceCheck);

        return $serviceCheck;
    }

    /**
     * @throws HttpException 404 when service check not found
     */
    public function findById(int $id): ServiceCheck
    {
        $serviceCheck = $this->serviceCheckRepository->findById($id);

        if ($serviceCheck === null) {
            throw new HttpException('Service check not found', 404);
        }

        return $serviceCheck;
    }

    /**
     * @throws HttpException 404 when service check not found
     */
    public function findBySlug(string $slug): ServiceCheck
    {
        $serviceCheck = $this->serviceCheckRepository->findBySlug($slug);

        if ($serviceCheck === null) {
            throw new HttpException('Service check not found', 404);
        }

        return $serviceCheck;
    }

    /**
     * @return list<ServiceCheck>
     */
    public function list(): array
    {
        return $this->serviceCheckRepository->list();
    }

    /**
     * Links a service check to a server.
     *
     * @throws HttpException 404 when server or service check not found
     */
    public function attachToServer(int $serverId, int $serviceCheckId): void
    {
        $server = $this->serverRepository->findById($serverId);

        if ($server === null) {
            throw new HttpException('Server not found', 404);
        }

        $serviceCheck = $this->serviceCheckRepository->findById($serviceCheckId);

        if ($serviceCheck === null) {
            throw new HttpException('Service check not found', 404);
        }

        if ($this->serverServiceCheckRepository->exists($serverId, $serviceCheckId)) {
            return;
        }

        $this->serverServiceCheckRepository->attach($serverId, $serviceCheckId);
    }

    /**
     * @return list<ServiceCheck>
     */
    public function listByServerId(int $serverId): array
    {
        $server = $this->serverRepository->findById($serverId);

        if ($server === null) {
            throw new HttpException('Server not found', 404);
        }

        return $this->serverServiceCheckRepository->listByServerId($serverId);
    }

    /**
     * @param array<string, mixed> $data Partial payload (name, slug, description)
     */
    private function applyDataToServiceCheck(ServiceCheck $serviceCheck, array $data): ServiceCheck
    {
        if (array_key_exists('name', $data)) {
            $serviceCheck->setName($data['name']);
        }

        if (array_key_exists('slug', $data)) {
            $serviceCheck->setSlug($data['slug']);
        }

        if (array_key_exists('description', $data)) {
            $serviceCheck->setDescription($data['description']);
        }

        return $serviceCheck;
    }
}
