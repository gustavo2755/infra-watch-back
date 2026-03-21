<?php

declare(strict_types=1);

namespace Tests\Http;

use Tests\HttpTestCase;

final class ServiceCheckControllerTest extends HttpTestCase
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

    public function testCreateSuccess(): void
    {
        $result = $this->request('POST', '/api/service-checks', [
            'name' => 'Redis',
            'slug' => 'redis',
            'description' => 'Cache server',
        ], [], $this->getAuthHeaders());

        $this->assertSame(201, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertSame('Service check created', $result['body']['message'] ?? '');
        $this->assertSame('Redis', $result['body']['data']['name'] ?? '');
        $this->assertSame('redis', $result['body']['data']['slug'] ?? '');
    }

    public function testUpdateSuccess(): void
    {
        $create = $this->request('POST', '/api/service-checks', [
            'name' => 'Memcached',
            'slug' => 'memcached',
            'description' => null,
        ], [], $this->getAuthHeaders());
        $id = $create['body']['data']['id'] ?? 0;

        $result = $this->request('PUT', '/api/service-checks/' . $id, [
            'name' => 'Memcached Updated',
            'slug' => 'memcached',
        ], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame('Memcached Updated', $result['body']['data']['name'] ?? '');
    }

    public function testFindById(): void
    {
        $create = $this->request('POST', '/api/service-checks', [
            'name' => 'Test',
            'slug' => 'test-sc',
            'description' => null,
        ], [], $this->getAuthHeaders());
        $id = $create['body']['data']['id'] ?? 0;

        $result = $this->request('GET', '/api/service-checks/' . $id, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame($id, $result['body']['data']['id'] ?? 0);
        $this->assertSame('test-sc', $result['body']['data']['slug'] ?? '');
    }

    public function testFindByIdNonexistent(): void
    {
        $result = $this->request('GET', '/api/service-checks/99999', [], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Service check not found', $result['body']['message'] ?? '');
    }

    public function testList(): void
    {
        $result = $this->request('GET', '/api/service-checks', [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertArrayHasKey('data', $result['body']['data']);
    }

    public function testFindBySlug(): void
    {
        $result = $this->request('GET', '/api/service-checks/slug/nginx', [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame('nginx', $result['body']['data']['slug'] ?? '');
    }

    public function testAttachToServer(): void
    {
        $serverPayload = [
            'name' => 'Web Server',
            'description' => null,
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
        $serverRes = $this->request('POST', '/api/servers', $serverPayload, [], $this->getAuthHeaders());
        $serverId = $serverRes['body']['data']['id'] ?? 0;
        $serviceCheckId = 1;

        $result = $this->request('POST', '/api/servers/' . $serverId . '/service-checks/' . $serviceCheckId, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
    }

    public function testValidationError(): void
    {
        $result = $this->request('POST', '/api/service-checks', [
            'name' => '',
            'slug' => '',
        ], [], $this->getAuthHeaders());

        $this->assertSame(422, $result['statusCode']);
        $this->assertArrayHasKey('errors', $result['body']);
    }

    public function testStandardizedResponse(): void
    {
        $result = $this->request('GET', '/api/service-checks', [], [], $this->getAuthHeaders());

        $this->assertArrayHasKey('success', $result['body']);
        $this->assertArrayHasKey('message', $result['body']);
        $this->assertArrayHasKey('data', $result['body']);
    }

    public function testFindBySlugNonexistent(): void
    {
        $result = $this->request('GET', '/api/service-checks/slug/nonexistent-slug-12345', [], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Service check not found', $result['body']['message'] ?? '');
    }

    public function testUpdateNonexistentServiceCheck(): void
    {
        $result = $this->request('PUT', '/api/service-checks/99999', [
            'name' => 'Updated',
            'slug' => 'updated',
        ], [], $this->getAuthHeaders());

        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Service check not found', $result['body']['message'] ?? '');
    }

    public function testUpdateWithEmptyPayload(): void
    {
        $create = $this->request('POST', '/api/service-checks', [
            'name' => 'EmptyUpdate',
            'slug' => 'empty-update',
            'description' => 'Original',
        ], [], $this->getAuthHeaders());
        $id = $create['body']['data']['id'] ?? 0;

        $result = $this->request('PUT', '/api/service-checks/' . $id, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame('EmptyUpdate', $result['body']['data']['name'] ?? '');
        $this->assertSame('Original', $result['body']['data']['description'] ?? '');
    }

    public function testAttachToServerAlreadyLinked(): void
    {
        $serverPayload = [
            'name' => 'Idempotent Server',
            'description' => null,
            'ip_address' => '192.168.1.10',
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
        $serverRes = $this->request('POST', '/api/servers', $serverPayload, [], $this->getAuthHeaders());
        $serverId = $serverRes['body']['data']['id'] ?? 0;
        $serviceCheckId = 1;

        $first = $this->request('POST', '/api/servers/' . $serverId . '/service-checks/' . $serviceCheckId, [], [], $this->getAuthHeaders());
        $second = $this->request('POST', '/api/servers/' . $serverId . '/service-checks/' . $serviceCheckId, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $first['statusCode']);
        $this->assertSame(200, $second['statusCode']);
    }

    public function testUpdateWithValidationError(): void
    {
        $create = $this->request('POST', '/api/service-checks', [
            'name' => 'ToUpdate',
            'slug' => 'to-update',
            'description' => null,
        ], [], $this->getAuthHeaders());
        $id = $create['body']['data']['id'] ?? 0;

        $result = $this->request('PUT', '/api/service-checks/' . $id, [
            'name' => '',
        ], [], $this->getAuthHeaders());

        $this->assertSame(422, $result['statusCode']);
        $this->assertArrayHasKey('errors', $result['body']);
    }
}
