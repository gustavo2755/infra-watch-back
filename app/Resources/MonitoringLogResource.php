<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\MonitoringLog;
use App\Models\MonitoringLogServiceCheck;

/**
 * Transforms a monitoring log into API output.
 */
final class MonitoringLogResource extends BaseResource
{
    /**
     * @param list<MonitoringLogServiceCheck> $serviceChecks
     */
    public function __construct(
        private readonly MonitoringLog $log,
        private readonly array $serviceChecks = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->log->getId(),
            'server_id' => $this->log->getServerId(),
            'checked_at' => $this->log->getCheckedAt(),
            'is_up' => $this->log->getIsUp(),
            'cpu_usage_percent' => $this->log->getCpuUsagePercent(),
            'ram_usage_percent' => $this->log->getRamUsagePercent(),
            'disk_usage_percent' => $this->log->getDiskUsagePercent(),
            'bandwidth_usage_percent' => $this->log->getBandwidthUsagePercent(),
            'is_alert' => $this->log->getIsAlert(),
            'alert_type' => $this->log->getAlertType(),
            'error_message' => $this->log->getErrorMessage(),
            'sent_to_email' => $this->log->getSentToEmail(),
            'created_at' => $this->log->getCreatedAt(),
            'updated_at' => $this->log->getUpdatedAt(),
            'service_checks' => array_map(
                fn (MonitoringLogServiceCheck $item) => MonitoringLogServiceCheckResource::make($item),
                $this->serviceChecks
            ),
        ];
    }

    /**
     * @param list<MonitoringLogServiceCheck> $serviceChecks
     * @return array<string, mixed>
     */
    public static function make(MonitoringLog $log, array $serviceChecks = []): array
    {
        return (new self($log, $serviceChecks))->toArray();
    }
}
