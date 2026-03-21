<?php

declare(strict_types=1);

namespace Tests\Http;

use Tests\HttpTestCase;

final class ServerControllerTest extends HttpTestCase
{
    private function getAuthHeaders(): array
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $result['body']['data']['token'] ?? '';

        return ['Authorization' => 'Bearer ' . $token];
    }

    private function validServerPayload(): array
    {
        return [
            'name' => 'Test Server',
            'description' => 'Test description',
            'ip_address' => '192.168.1.1',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 4,
            'ram_total' => 8,
            'disk_total' => 100,
            'check_interval_seconds' => 60,
            'retention_days' => 30,
            'cpu_alert_threshold' => 90,
            'ram_alert_threshold' => 90,
            'disk_alert_threshold' => 90,
            'bandwidth_alert_threshold' => 100,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
        ];
    }

    public function testCreateSuccess(): void
    {
        $result = $this->request('POST', '/api/servers', $this->validServerPayload(), [], $this->getAuthHeaders());

        $this->assertSame(201, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertSame('Server created', $result['body']['message'] ?? '');
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertSame('Test Server', $result['body']['data']['name'] ?? '');
        $this->assertSame('192.168.1.1', $result['body']['data']['ip_address'] ?? '');
    }

    public function testUpdateSuccess(): void
    {
        $create = $this->request('POST', '/api/servers', $this->validServerPayload(), [], $this->getAuthHeaders());
        $serverId = $create['body']['data']['id'] ?? 0;

        $result = $this->request('PUT', '/api/servers/' . $serverId, [
            'name' => 'Updated Server',
            'ip_address' => '10.0.0.1',
        ], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertSame('Updated Server', $result['body']['data']['name'] ?? '');
        $this->assertSame('10.0.0.1', $result['body']['data']['ip_address'] ?? '');
    }

    public function testFindByIdExisting(): void
    {
        $create = $this->request('POST', '/api/servers', $this->validServerPayload(), [], $this->getAuthHeaders());
        $serverId = $create['body']['data']['id'] ?? 0;

        $result = $this->request('GET', '/api/servers/' . $serverId, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame($serverId, $result['body']['data']['id'] ?? 0);
    }

    public function testFindByIdNonexistent(): void
    {
        $result = $this->request('GET', '/api/servers/99999', [], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Server not found', $result['body']['message'] ?? '');
    }

    public function testList(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertArrayHasKey('data', $result['body']['data']);
        $this->assertIsArray($result['body']['data']['data']);
    }

    public function testFilterByName(): void
    {
        $this->request('POST', '/api/servers', array_merge($this->validServerPayload(), ['name' => 'Alpha Server']), [], $this->getAuthHeaders());
        $this->request('POST', '/api/servers', array_merge($this->validServerPayload(), ['name' => 'Beta Server', 'ip_address' => '192.168.1.2']), [], $this->getAuthHeaders());

        $result = $this->request('GET', '/api/servers', [], ['name' => 'Alpha'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertGreaterThanOrEqual(1, count($result['body']['data']['data'] ?? []));
    }

    public function testFilterByIsActive(): void
    {
        $result = $this->request('GET', '/api/servers', [], ['is_active' => '1'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
    }

    public function testValidationError(): void
    {
        $result = $this->request('POST', '/api/servers', [
            'name' => '',
            'ip_address' => 'invalid',
        ], [], $this->getAuthHeaders());

        $this->assertSame(422, $result['statusCode']);
        $this->assertArrayHasKey('errors', $result['body']);
    }

    public function testMethodNotAllowed(): void
    {
        $result = $this->request('DELETE', '/api/servers/1', [], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
    }

    public function testStandardizedResponse(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], $this->getAuthHeaders());

        $this->assertArrayHasKey('success', $result['body']);
        $this->assertArrayHasKey('message', $result['body']);
        $this->assertArrayHasKey('data', $result['body']);
    }

    public function testUpdateNonexistentServer(): void
    {
        $result = $this->request('PUT', '/api/servers/99999', [
            'name' => 'Updated',
            'ip_address' => '10.0.0.1',
        ], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Server not found', $result['body']['message'] ?? '');
    }

    public function testUpdateWithValidationError(): void
    {
        $create = $this->request('POST', '/api/servers', $this->validServerPayload(), [], $this->getAuthHeaders());
        $serverId = $create['body']['data']['id'] ?? 0;

        $result = $this->request('PUT', '/api/servers/' . $serverId, [
            'ip_address' => '999.999.999.999',
        ], [], $this->getAuthHeaders());

        $this->assertSame(422, $result['statusCode']);
        $this->assertArrayHasKey('errors', $result['body']);
    }

    public function testFilterByIsActiveWithTrue(): void
    {
        $result = $this->request('GET', '/api/servers', [], ['is_active' => 'true'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
    }

    public function testFilterByIsActiveWithOn(): void
    {
        $result = $this->request('GET', '/api/servers', [], ['is_active' => 'on'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
    }
}
