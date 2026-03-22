<?php

declare(strict_types=1);

namespace Tests\Http;

use Tests\HttpTestCase;

final class MonitoringLogControllerTest extends HttpTestCase
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

    private function createServerPayload(float $threshold = 90.0): array
    {
        return [
            'name' => 'Monitoring Server',
            'description' => 'for logs',
            'ip_address' => '10.1.1.1',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 4,
            'ram_total' => 8,
            'disk_total' => 100,
            'check_interval_seconds' => 60,
            'retention_days' => 30,
            'cpu_alert_threshold' => $threshold,
            'ram_alert_threshold' => $threshold,
            'disk_alert_threshold' => $threshold,
            'bandwidth_alert_threshold' => $threshold,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
        ];
    }

    private function createAndProcessServer(float $threshold = 90.0): int
    {
        $create = $this->request('POST', '/api/servers', $this->createServerPayload($threshold), [], $this->getAuthHeaders());
        $serverId = (int) ($create['body']['data']['id'] ?? 0);
        $this->request('POST', '/api/servers/' . $serverId . '/service-checks/1', [], [], $this->getAuthHeaders());
        $this->container->getQueueService()->runCycle();

        return $serverId;
    }

    public function testListMonitoringLogsRequiresAuthentication(): void
    {
        $result = $this->request('GET', '/api/monitoring-logs');
        $this->assertSame(401, $result['statusCode']);
    }

    public function testListMonitoringLogs(): void
    {
        $this->createAndProcessServer();

        $result = $this->request('GET', '/api/monitoring-logs', [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertArrayHasKey('data', $result['body']['data']);
        $this->assertArrayHasKey('meta', $result['body']['data']);
        $this->assertSame(50, $result['body']['data']['meta']['per_page'] ?? null);
        $this->assertGreaterThanOrEqual(1, $result['body']['data']['count'] ?? 0);
    }

    public function testListMonitoringLogsByServerFilter(): void
    {
        $serverA = $this->createAndProcessServer();
        $serverB = $this->createAndProcessServer();

        $result = $this->request('GET', '/api/monitoring-logs', [], ['server_id' => (string) $serverA], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        foreach ($result['body']['data']['data'] as $item) {
            $this->assertSame($serverA, $item['server_id']);
        }
        $this->assertNotSame($serverA, $serverB);
    }

    public function testListMonitoringLogsByServerEndpointPagination(): void
    {
        $serverId = $this->createAndProcessServer();
        $this->container->getQueueService()->runCycle();

        $result = $this->request(
            'GET',
            '/api/servers/' . $serverId . '/monitoring-logs',
            [],
            ['page' => '1', 'per_page' => '1'],
            $this->getAuthHeaders()
        );

        $this->assertSame(200, $result['statusCode']);
        $this->assertCount(1, $result['body']['data']['data'] ?? []);
        $this->assertSame(1, $result['body']['data']['meta']['per_page'] ?? null);
    }

    public function testListMonitoringAlertsOnly(): void
    {
        $this->createAndProcessServer(-1);

        $result = $this->request('GET', '/api/monitoring-logs', [], ['alerts_only' => 'true'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertGreaterThanOrEqual(1, $result['body']['data']['count'] ?? 0);
        foreach ($result['body']['data']['data'] as $item) {
            $this->assertSame(true, $item['is_alert']);
        }
    }

    public function testShowMonitoringLogDetails(): void
    {
        $this->createAndProcessServer();
        $list = $this->request('GET', '/api/monitoring-logs', [], [], $this->getAuthHeaders());
        $logId = (int) ($list['body']['data']['data'][0]['id'] ?? 0);

        $result = $this->request('GET', '/api/monitoring-logs/' . $logId, [], [], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame($logId, $result['body']['data']['id'] ?? 0);
        $this->assertArrayHasKey('service_checks', $result['body']['data']);
    }

    public function testShowMonitoringLogReturns404WhenNotFound(): void
    {
        $result = $this->request('GET', '/api/monitoring-logs/999999', [], [], $this->getAuthHeaders());
        $this->assertSame(404, $result['statusCode']);
        $this->assertSame('Monitoring log not found', $result['body']['message'] ?? '');
    }

    public function testDashboardEndpoint(): void
    {
        $serverId = $this->createAndProcessServer();

        $result = $this->request(
            'GET',
            '/api/servers/' . $serverId . '/monitoring-logs/dashboard',
            [],
            ['page' => '1', 'per_page' => '5'],
            $this->getAuthHeaders()
        );

        $this->assertSame(200, $result['statusCode']);
        $this->assertArrayHasKey('data', $result['body']['data']);
        $this->assertArrayHasKey('meta', $result['body']['data']);
        $this->assertSame(5, $result['body']['data']['meta']['per_page'] ?? null);
        $this->assertLessThanOrEqual(5, count($result['body']['data']['data'] ?? []));
    }

    public function testMonitoringLogsPaginationWithPageAndPerPage(): void
    {
        $this->createAndProcessServer();
        $this->createAndProcessServer();
        $this->createAndProcessServer();

        $result = $this->request('GET', '/api/monitoring-logs', [], ['page' => '1', 'per_page' => '2'], $this->getAuthHeaders());

        $this->assertSame(200, $result['statusCode']);
        $this->assertCount(2, $result['body']['data']['data'] ?? []);
        $this->assertSame(1, $result['body']['data']['meta']['page'] ?? null);
        $this->assertSame(2, $result['body']['data']['meta']['per_page'] ?? null);
        $this->assertGreaterThanOrEqual(3, $result['body']['data']['meta']['total'] ?? 0);
    }
}
