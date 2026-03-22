<?php

declare(strict_types=1);

namespace Tests\Http;

use Tests\HttpTestCase;

final class MonitoringFlowTest extends HttpTestCase
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

    public function testEndToEndMonitoringFlow(): void
    {
        $headers = $this->getAuthHeaders();

        $createServer = $this->request('POST', '/api/servers', [
            'name' => 'EndToEnd Server',
            'description' => 'flow',
            'ip_address' => '10.2.2.2',
            'is_active' => true,
            'monitor_resources' => true,
            'cpu_total' => 4,
            'ram_total' => 8,
            'disk_total' => 100,
            'check_interval_seconds' => 60,
            'retention_days' => 30,
            'cpu_alert_threshold' => -1,
            'ram_alert_threshold' => -1,
            'disk_alert_threshold' => -1,
            'bandwidth_alert_threshold' => -1,
            'alert_cpu_enabled' => true,
            'alert_ram_enabled' => true,
            'alert_disk_enabled' => true,
            'alert_bandwidth_enabled' => true,
        ], [], $headers);
        $this->assertSame(201, $createServer['statusCode']);
        $serverId = (int) ($createServer['body']['data']['id'] ?? 0);

        $attach = $this->request('POST', '/api/servers/' . $serverId . '/service-checks/1', [], [], $headers);
        $this->assertSame(200, $attach['statusCode']);

        $processed = $this->container->getQueueService()->runCycle();
        $this->assertSame(1, $processed);

        $logs = $this->request('GET', '/api/servers/' . $serverId . '/monitoring-logs', [], [], $headers);
        $this->assertSame(200, $logs['statusCode']);
        $this->assertGreaterThanOrEqual(1, $logs['body']['data']['count'] ?? 0);

        $logId = (int) ($logs['body']['data']['data'][0]['id'] ?? 0);
        $logDetails = $this->request('GET', '/api/monitoring-logs/' . $logId, [], [], $headers);
        $this->assertSame(200, $logDetails['statusCode']);
        $this->assertSame(true, $logDetails['body']['data']['is_alert']);
        $this->assertArrayHasKey('service_checks', $logDetails['body']['data']);
        $this->assertGreaterThanOrEqual(1, count($logDetails['body']['data']['service_checks'] ?? []));
    }
}
