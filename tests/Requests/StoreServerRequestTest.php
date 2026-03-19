<?php

declare(strict_types=1);

namespace Tests\Requests;

use App\Exceptions\ValidationException;
use App\Requests\StoreServerRequest;
use PHPUnit\Framework\TestCase;

final class StoreServerRequestTest extends TestCase
{
    private StoreServerRequest $request;

    private function validPayload(): array
    {
        return [
            'name' => 'Server 1',
            'description' => 'Test server',
            'ip_address' => '192.168.1.1',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 4.0,
            'ram_total' => 8.0,
            'disk_total' => 100.0,
            'check_interval_seconds' => 60,
            'last_check_at' => null,
            'retention_days' => 30,
            'cpu_alert_threshold' => 90,
            'ram_alert_threshold' => 90,
            'disk_alert_threshold' => 90,
            'bandwidth_alert_threshold' => 80,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
            'created_by' => 1,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreServerRequest();
    }

    public function testValidPayload(): void
    {
        $data = $this->validPayload();
        $result = $this->request->validate($data);
        $this->assertSame('Server 1', $result['name']);
        $this->assertSame('192.168.1.1', $result['ip_address']);
        $this->assertSame(1, $result['created_by']);
    }

    public function testRequiredFieldAbsent(): void
    {
        $data = $this->validPayload();
        unset($data['name']);
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidIpAddress(): void
    {
        $data = $this->validPayload();
        $data['ip_address'] = '999.999.999.999';
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidBoolean(): void
    {
        $data = $this->validPayload();
        $data['is_active'] = 'invalid';
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidInteger(): void
    {
        $data = $this->validPayload();
        $data['check_interval_seconds'] = 'not-a-number';
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidString(): void
    {
        $data = $this->validPayload();
        $data['name'] = 123;
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testMalformedPayload(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['value1', 'value2']);
    }

    public function testValidWithLastCheckAt(): void
    {
        $data = $this->validPayload();
        $data['last_check_at'] = '2024-01-15 10:30:00';
        $result = $this->request->validate($data);
        $this->assertSame('2024-01-15 10:30:00', $result['last_check_at']);
    }
}
