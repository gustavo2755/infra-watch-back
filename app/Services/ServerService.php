<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Models\Server;
use App\Repositories\ServerRepository;
use App\Repositories\UserRepository;

/**
 * Service for server business logic.
 */
final class ServerService
{
    public function __construct(
        private ServerRepository $serverRepository,
        private UserRepository $userRepository
    ) {
    }

    /**
     * Creates a server from validated payload.
     *
     * @param array<string, mixed> $data Validated from StoreServerRequest
     * @throws HttpException 404 when created_by user does not exist
     */
    public function create(array $data): Server
    {
        $createdBy = isset($data['created_by']) ? (int) $data['created_by'] : null;

        if ($createdBy !== null) {
            $user = $this->userRepository->findById($createdBy);

            if ($user === null) {
                throw new HttpException('User not found', 404);
            }
        }

        $server = Server::fromArray($data);

        $id = $this->serverRepository->create($server);
        $server->setId($id);

        return $server;
    }

    /**
     * Updates a server from validated payload.
     *
     * @param array<string, mixed> $data Validated from UpdateServerRequest
     * @throws HttpException 404 when server or created_by user does not exist
     */
    public function update(int $id, array $data): Server
    {
        $server = $this->serverRepository->findById($id);

        if ($server === null) {
            throw new HttpException('Server not found', 404);
        }

        $createdBy = $data['created_by'] ?? $server->getCreatedBy();

        if ($createdBy !== null) {
            $user = $this->userRepository->findById((int) $createdBy);

            if ($user === null) {
                throw new HttpException('User not found', 404);
            }
        }

        $server = $this->applyDataToServer($server, $data);

        $server->setId($id);

        $this->serverRepository->update($server);

        return $server;
    }

    /**
     * @throws HttpException 404 when server not found
     */
    public function findById(int $id): Server
    {
        $server = $this->serverRepository->findById($id);

        if ($server === null) {
            throw new HttpException('Server not found', 404);
        }

        return $server;
    }

    /**
     * @return list<Server>
     */
    public function list(): array
    {
        return $this->serverRepository->list();
    }

    /**
     * @return list<Server>
     */
    public function filterByName(string $name): array
    {
        return $this->serverRepository->filterByName($name);
    }

    /**
     * @return list<Server>
     */
    public function filterByIsActive(bool $isActive): array
    {
        return $this->serverRepository->filterByIsActive($isActive);
    }

    /**
     * @param array<string, mixed> $data Partial payload (e.g. from UpdateServerRequest)
     */
    private function applyDataToServer(Server $server, array $data): Server
    {
        if (array_key_exists('name', $data)) {
            $server->setName($data['name']);
        }

        if (array_key_exists('description', $data)) {
            $server->setDescription($data['description']);
        }

        if (array_key_exists('ip_address', $data)) {
            $server->setIpAddress($data['ip_address']);
        }

        if (array_key_exists('is_active', $data)) {
            $server->setIsActive((bool) $data['is_active']);
        }

        if (array_key_exists('monitor_resources', $data)) {
            $server->setMonitorResources((bool) $data['monitor_resources']);
        }

        if (array_key_exists('cpu_total', $data)) {
            $server->setCpuTotal($data['cpu_total'] !== null ? (float) $data['cpu_total'] : null);
        }

        if (array_key_exists('ram_total', $data)) {
            $server->setRamTotal($data['ram_total'] !== null ? (float) $data['ram_total'] : null);
        }

        if (array_key_exists('disk_total', $data)) {
            $server->setDiskTotal($data['disk_total'] !== null ? (float) $data['disk_total'] : null);
        }

        if (array_key_exists('check_interval_seconds', $data)) {
            $server->setCheckIntervalSeconds($data['check_interval_seconds'] !== null ? (int) $data['check_interval_seconds'] : null);
        }

        if (array_key_exists('last_check_at', $data)) {
            $server->setLastCheckAt($data['last_check_at']);
        }

        if (array_key_exists('retention_days', $data)) {
            $server->setRetentionDays($data['retention_days'] !== null ? (int) $data['retention_days'] : null);
        }

        if (array_key_exists('cpu_alert_threshold', $data)) {
            $server->setCpuAlertThreshold($data['cpu_alert_threshold'] !== null ? (float) $data['cpu_alert_threshold'] : null);
        }

        if (array_key_exists('ram_alert_threshold', $data)) {
            $server->setRamAlertThreshold($data['ram_alert_threshold'] !== null ? (float) $data['ram_alert_threshold'] : null);
        }

        if (array_key_exists('disk_alert_threshold', $data)) {
            $server->setDiskAlertThreshold($data['disk_alert_threshold'] !== null ? (float) $data['disk_alert_threshold'] : null);
        }

        if (array_key_exists('bandwidth_alert_threshold', $data)) {
            $server->setBandwidthAlertThreshold($data['bandwidth_alert_threshold'] !== null ? (float) $data['bandwidth_alert_threshold'] : null);
        }

        if (array_key_exists('alert_cpu_enabled', $data)) {
            $server->setAlertCpuEnabled((bool) $data['alert_cpu_enabled']);
        }

        if (array_key_exists('alert_ram_enabled', $data)) {
            $server->setAlertRamEnabled((bool) $data['alert_ram_enabled']);
        }

        if (array_key_exists('alert_disk_enabled', $data)) {
            $server->setAlertDiskEnabled((bool) $data['alert_disk_enabled']);
        }

        if (array_key_exists('alert_bandwidth_enabled', $data)) {
            $server->setAlertBandwidthEnabled((bool) $data['alert_bandwidth_enabled']);
        }

        if (array_key_exists('created_by', $data)) {
            $server->setCreatedBy($data['created_by'] !== null ? (int) $data['created_by'] : null);
        }

        return $server;
    }
}
