<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\MonitoringLog;
use App\Models\MonitoringLogServiceCheck;
use App\Resources\MonitoringLogCollectionResource;
use App\Resources\MonitoringLogResource;
use PHPUnit\Framework\TestCase;

final class MonitoringLogResourceTest extends TestCase
{
    public function testTransformsMonitoringLogWithServiceChecks(): void
    {
        $log = new MonitoringLog(
            10,
            2,
            '2026-01-01 10:00:00',
            true,
            20.5,
            30.5,
            40.5,
            10.5,
            true,
            'cpu',
            null,
            'alerts@infra.watch',
            '2026-01-01 10:00:00',
            '2026-01-01 10:00:00'
        );
        $serviceChecks = [
            new MonitoringLogServiceCheck(1, 10, 5, true, 'Service is running', '2026-01-01 10:00:00', '2026-01-01 10:00:00'),
        ];

        $result = MonitoringLogResource::make($log, $serviceChecks);

        $this->assertSame(10, $result['id']);
        $this->assertSame(2, $result['server_id']);
        $this->assertSame('2026-01-01 10:00:00', $result['checked_at']);
        $this->assertSame(true, $result['is_up']);
        $this->assertSame(20.5, $result['cpu_usage_percent']);
        $this->assertSame('cpu', $result['alert_type']);
        $this->assertArrayHasKey('service_checks', $result);
        $this->assertCount(1, $result['service_checks']);
        $this->assertSame(5, $result['service_checks'][0]['service_check_id']);
    }

    public function testTransformsCollection(): void
    {
        $logs = [
            new MonitoringLog(1, 1, '2026-01-01 10:00:00', true),
            new MonitoringLog(2, 1, '2026-01-01 11:00:00', true),
        ];

        $result = MonitoringLogCollectionResource::make($logs);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['count']);
        $this->assertNull($result['meta']);
    }

    public function testTransformsCollectionWithPaginationMeta(): void
    {
        $logs = [
            new MonitoringLog(1, 1, '2026-01-01 10:00:00', true),
        ];
        $meta = [
            'page' => 1,
            'per_page' => 50,
            'total' => 1,
            'total_pages' => 1,
            'has_next' => false,
            'has_prev' => false,
        ];

        $result = MonitoringLogCollectionResource::makePaginated($logs, $meta);

        $this->assertSame($meta, $result['meta']);
    }
}
