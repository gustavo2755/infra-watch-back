<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\MonitoringLogServiceCheck;
use App\Resources\MonitoringLogServiceCheckResource;
use PHPUnit\Framework\TestCase;

final class MonitoringLogServiceCheckResourceTest extends TestCase
{
    public function testTransformsSingleResult(): void
    {
        $result = new MonitoringLogServiceCheck(
            99,
            10,
            8,
            false,
            'Service is not running',
            '2026-01-01 10:00:00',
            '2026-01-01 10:00:00'
        );

        $data = MonitoringLogServiceCheckResource::make($result);

        $this->assertSame(99, $data['id']);
        $this->assertSame(10, $data['monitoring_log_id']);
        $this->assertSame(8, $data['service_check_id']);
        $this->assertSame(false, $data['is_running']);
        $this->assertSame('Service is not running', $data['output_message']);
    }

    public function testTransformsCollection(): void
    {
        $results = [
            new MonitoringLogServiceCheck(1, 1, 1, true, 'Service is running'),
            new MonitoringLogServiceCheck(2, 1, 2, false, 'Service is not running'),
        ];

        $data = MonitoringLogServiceCheckResource::collection($results);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertCount(2, $data['data']);
        $this->assertSame(2, $data['count']);
    }
}
