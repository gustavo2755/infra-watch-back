<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Server;
use App\Models\ServiceCheck;

/**
 * Transforms a single Server model into API output.
 */
final class ServerResource extends BaseResource
{
    /**
     * @param list<ServiceCheck> $serviceChecks
     */
    public function __construct(
        private readonly Server $server,
        private readonly array $serviceChecks = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->server->getId(),
            'name' => $this->server->getName(),
            'description' => $this->server->getDescription(),
            'ip_address' => $this->server->getIpAddress(),
            'is_active' => $this->server->getIsActive(),
            'monitor_resources' => $this->server->getMonitorResources(),
            'cpu_total' => $this->server->getCpuTotal(),
            'ram_total' => $this->server->getRamTotal(),
            'disk_total' => $this->server->getDiskTotal(),
            'check_interval_seconds' => $this->server->getCheckIntervalSeconds(),
            'last_check_at' => $this->server->getLastCheckAt(),
            'retention_days' => $this->server->getRetentionDays(),
            'cpu_alert_threshold' => $this->server->getCpuAlertThreshold(),
            'ram_alert_threshold' => $this->server->getRamAlertThreshold(),
            'disk_alert_threshold' => $this->server->getDiskAlertThreshold(),
            'bandwidth_alert_threshold' => $this->server->getBandwidthAlertThreshold(),
            'alert_cpu_enabled' => $this->server->getAlertCpuEnabled(),
            'alert_ram_enabled' => $this->server->getAlertRamEnabled(),
            'alert_disk_enabled' => $this->server->getAlertDiskEnabled(),
            'alert_bandwidth_enabled' => $this->server->getAlertBandwidthEnabled(),
            'created_by' => $this->server->getCreatedBy(),
            'created_at' => $this->server->getCreatedAt(),
            'updated_at' => $this->server->getUpdatedAt(),
        ];

        $data['service_checks'] = array_map(fn (ServiceCheck $sc) => ServiceCheckResource::make($sc), $this->serviceChecks);

        return $data;
    }

    /**
     * @param list<ServiceCheck> $serviceChecks
     * @return array<string, mixed>
     */
    public static function make(Server $server, array $serviceChecks = []): array
    {
        return (new self($server, $serviceChecks))->toArray();
    }
}
