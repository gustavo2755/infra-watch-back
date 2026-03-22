<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\MonitoringLog;

/**
 * Transforms a list of monitoring logs into API output.
 */
final class MonitoringLogCollectionResource extends BaseResource
{
    /**
     * @param list<MonitoringLog> $logs
     */
    public function __construct(
        private readonly array $logs,
        private readonly ?array $meta = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_map(fn (MonitoringLog $log) => MonitoringLogResource::make($log), $this->logs);

        return [
            'data' => $data,
            'count' => count($data),
            'meta' => $this->meta,
        ];
    }

    /**
     * @param list<MonitoringLog> $logs
     * @return array<string, mixed>
     */
    public static function make(array $logs): array
    {
        return (new self($logs))->toArray();
    }

    /**
     * @param list<MonitoringLog> $logs
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    public static function makePaginated(array $logs, array $meta): array
    {
        return (new self($logs, $meta))->toArray();
    }
}
