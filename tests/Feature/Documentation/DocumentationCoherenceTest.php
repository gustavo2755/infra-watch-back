<?php

declare(strict_types=1);

namespace Tests\Documentation;

use App\OpenApi\OpenApiSpec;
use Tests\HttpTestCase;

final class DocumentationCoherenceTest extends HttpTestCase
{
    public function testDocumentedEndpointsExist(): void
    {
        $spec = (new OpenApiSpec())->toArray();
        $paths = $spec['paths'] ?? [];
        $loginResult = $this->request('POST', '/api/auth/login', ['email' => 'test@example.com', 'password' => 'password123']);
        $token = $loginResult['body']['data']['token'] ?? null;
        $authHeaders = $token !== null ? ['Authorization' => 'Bearer ' . $token] : [];

        $pathParamValues = [
            'id' => '1',
            'slug' => 'nginx',
            'serverId' => '1',
            'serviceCheckId' => '1',
        ];

        foreach ($paths as $pathTemplate => $operations) {
            foreach ($operations as $methodLower => $op) {
                $method = strtoupper($methodLower);
                $path = $pathTemplate;
                foreach ($pathParamValues as $param => $value) {
                    $path = str_replace('{' . $param . '}', $value, $path);
                }

                $security = $op['security'] ?? $spec['security'] ?? [];
                $needsAuth = $security !== [];
                $headers = $needsAuth ? $authHeaders : [];

                $body = [];
                if (in_array($method, ['POST', 'PUT'], true)) {
                    if ($path === '/api/auth/login') {
                        $body = ['email' => 'x@y.z', 'password' => 'p'];
                    } elseif (str_contains($path, '/api/servers') && !str_contains($path, 'service-checks')) {
                        if ($method === 'POST') {
                            $body = [
                                'name' => 'Test',
                                'ip_address' => '192.168.1.1',
                                'is_active' => true,
                                'monitor_resources' => false,
                                'cpu_total' => 2,
                                'ram_total' => 4,
                                'disk_total' => 50,
                                'check_interval_seconds' => 60,
                                'retention_days' => 30,
                                'cpu_alert_threshold' => 90,
                                'ram_alert_threshold' => 90,
                                'disk_alert_threshold' => 90,
                                'bandwidth_alert_threshold' => 100,
                                'alert_cpu_enabled' => false,
                                'alert_ram_enabled' => false,
                                'alert_disk_enabled' => false,
                                'alert_bandwidth_enabled' => false,
                            ];
                        } else {
                            $body = ['name' => 'Updated'];
                        }
                    } elseif (str_contains($path, '/api/service-checks') && !str_contains($path, 'servers/')) {
                        if ($method === 'POST') {
                            $body = ['name' => 'Test', 'slug' => 'test-slug', 'description' => null];
                        } else {
                            $body = ['name' => 'Updated'];
                        }
                    }
                }

                $result = $this->request($method, $path, $body, [], $headers);

                $isRouter404 = $result['statusCode'] === 404 && ($result['body']['message'] ?? '') === 'Not found';
                $this->assertFalse($isRouter404, "Documented endpoint $method $pathTemplate should exist, got router 404");
            }
        }
    }

    public function testLoginRequestBodyCoherent(): void
    {
        $spec = (new OpenApiSpec())->toArray();
        $loginSchema = $spec['components']['schemas']['LoginRequest'] ?? [];
        $this->assertArrayHasKey('properties', $loginSchema);
        $this->assertArrayHasKey('email', $loginSchema['properties'] ?? []);
        $this->assertArrayHasKey('password', $loginSchema['properties'] ?? []);

        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame(200, $result['statusCode']);
        $this->assertNotEmpty($result['body']['data']['token'] ?? '');
    }

    public function testSuccessResponseStructure(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame(200, $result['statusCode']);
        $this->assertArrayHasKey('success', $result['body']);
        $this->assertArrayHasKey('message', $result['body']);
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertTrue($result['body']['success']);
        $this->assertSame('Login successful', $result['body']['message']);
    }
}
