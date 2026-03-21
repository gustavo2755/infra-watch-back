<?php

declare(strict_types=1);

namespace Tests\Http;

use Firebase\JWT\JWT;
use Tests\HttpTestCase;

final class AuthMiddlewareTest extends HttpTestCase
{
    private function getTokenAfterLogin(): string
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        return $result['body']['data']['token'] ?? '';
    }

    public function testAllowAccessWhenAuthenticated(): void
    {
        $token = $this->getTokenAfterLogin();
        $headers = ['Authorization' => 'Bearer ' . $token];

        $result = $this->request('GET', '/api/servers', [], [], $headers);

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
    }

    public function testBlockAccessWhenNotAuthenticated(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], []);

        $this->assertSame(401, $result['statusCode']);
        $this->assertFalse($result['body']['success'] ?? true);
        $this->assertSame('Unauthorized', $result['body']['message'] ?? '');
    }

    public function testProtectedRouteReturnsAdequateError(): void
    {
        $result = $this->request('POST', '/api/servers', [
            'name' => 'Test',
            'ip_address' => '192.168.1.1',
        ], [], []);

        $this->assertSame(401, $result['statusCode']);
        $this->assertArrayHasKey('message', $result['body']);
    }

    public function testBlockAccessWhenAuthorizationHeaderInvalidFormat(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], [
            'Authorization' => 'Basic xxx',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertSame('Invalid authorization header', $result['body']['message'] ?? '');
    }

    public function testBlockAccessWhenAuthorizationHeaderTokenScheme(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], [
            'Authorization' => 'Token some-token',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertSame('Invalid authorization header', $result['body']['message'] ?? '');
    }

    public function testBlockAccessWhenAuthorizationHeaderBearerOnly(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], [
            'Authorization' => 'Bearer ',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertArrayHasKey('message', $result['body']);
    }

    public function testBlockAccessWhenTokenExpired(): void
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'test-secret-at-least-32-chars-for-hs256';
        $now = time();
        $payload = [
            'user_id' => 1,
            'iat' => $now - 3600,
            'exp' => $now - 60,
        ];
        $token = JWT::encode($payload, $secret, 'HS256');

        $result = $this->request('GET', '/api/servers', [], [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertSame('Token expired', $result['body']['message'] ?? '');
    }

    public function testBlockAccessWhenTokenInvalid(): void
    {
        $result = $this->request('GET', '/api/servers', [], [], [
            'Authorization' => 'Bearer not.a.valid.jwt',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertSame('Invalid token', $result['body']['message'] ?? '');
    }
}
