<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\MonitoringLogServiceCheck;

/**
 * Transforms monitoring log service check results into API output.
 */
final class MonitoringLogServiceCheckResource extends BaseResource
{
    public function __construct(
        private readonly MonitoringLogServiceCheck $result
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->result->getId(),
            'monitoring_log_id' => $this->result->getMonitoringLogId(),
            'service_check_id' => $this->result->getServiceCheckId(),
            'is_running' => $this->result->getIsRunning(),
            'output_message' => $this->result->getOutputMessage(),
            'created_at' => $this->result->getCreatedAt(),
            'updated_at' => $this->result->getUpdatedAt(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function make(MonitoringLogServiceCheck $result): array
    {
        return (new self($result))->toArray();
    }

    /**
     * @param list<MonitoringLogServiceCheck> $results
     * @return array<string, mixed>
     */
    public static function collection(array $results): array
    {
        $data = array_map(fn (MonitoringLogServiceCheck $item) => self::make($item), $results);

        return [
            'data' => $data,
            'count' => count($data),
        ];
    }
}
