<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\Server;
use App\Resources\ServerResource;
use PHPUnit\Framework\TestCase;

final class ServerResourceTest extends TestCase
{
    public function testTransformsSingleServer(): void
    {
        $server = new Server(
            1,
            'Web Server',
            'Main web server',
            '192.168.1.1',
            true,
            true,
            4.0,
            8.0,
            100.0,
            60,
            '2025-01-01 12:00:00',
            30,
            90.0,
            90.0,
            90.0,
            100.0,
            true,
            true,
            true,
            true,
            1,
            '2025-01-01 10:00:00',
            '2025-01-01 10:00:00'
        );

        $result = ServerResource::make($server);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('Web Server', $result['name']);
        $this->assertSame('Main web server', $result['description']);
        $this->assertSame('192.168.1.1', $result['ip_address']);
        $this->assertSame(true, $result['is_active']);
        $this->assertSame(true, $result['monitor_resources']);
        $this->assertSame(4.0, $result['cpu_total']);
        $this->assertSame(8.0, $result['ram_total']);
        $this->assertSame(100.0, $result['disk_total']);
        $this->assertSame(60, $result['check_interval_seconds']);
        $this->assertSame('2025-01-01 12:00:00', $result['last_check_at']);
        $this->assertSame(30, $result['retention_days']);
        $this->assertSame(90.0, $result['cpu_alert_threshold']);
        $this->assertSame(90.0, $result['ram_alert_threshold']);
        $this->assertSame(90.0, $result['disk_alert_threshold']);
        $this->assertSame(100.0, $result['bandwidth_alert_threshold']);
        $this->assertSame(true, $result['alert_cpu_enabled']);
        $this->assertSame(true, $result['alert_ram_enabled']);
        $this->assertSame(true, $result['alert_disk_enabled']);
        $this->assertSame(true, $result['alert_bandwidth_enabled']);
        $this->assertSame(1, $result['created_by']);
        $this->assertSame('2025-01-01 10:00:00', $result['created_at']);
        $this->assertSame('2025-01-01 10:00:00', $result['updated_at']);
    }

    public function testExposesCorrectFields(): void
    {
        $server = new Server(42, 'Test', null, '10.0.0.1');
        $result = ServerResource::make($server);

        $expectedKeys = [
            'id', 'name', 'description', 'ip_address', 'is_active', 'monitor_resources',
            'cpu_total', 'ram_total', 'disk_total', 'check_interval_seconds', 'last_check_at',
            'retention_days', 'cpu_alert_threshold', 'ram_alert_threshold', 'disk_alert_threshold',
            'bandwidth_alert_threshold', 'alert_cpu_enabled', 'alert_ram_enabled',
            'alert_disk_enabled', 'alert_bandwidth_enabled', 'created_by', 'created_at', 'updated_at',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }

        $this->assertCount(count($expectedKeys), $result);
    }

    public function testNoUndueFields(): void
    {
        $server = new Server(1, 'Test', null, '1.1.1.1');
        $result = ServerResource::make($server);

        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('internal_id', $result);
    }
}
