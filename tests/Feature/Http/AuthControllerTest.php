<?php

declare(strict_types=1);

namespace Tests\Http;

use Tests\HttpTestCase;

final class AuthControllerTest extends HttpTestCase
{
    public function testLoginSuccess(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertSame('Login successful', $result['body']['message'] ?? '');
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertArrayHasKey('token', $result['body']['data']);
        $this->assertNotEmpty($result['body']['data']['token'] ?? '');
        $this->assertArrayHasKey('user_id', $result['body']['data']);
        $this->assertSame('test@example.com', $result['body']['data']['email'] ?? '');
    }

    public function testLoginEmailNotFound(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'any',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertFalse($result['body']['success'] ?? true);
        $this->assertSame('Invalid credentials', $result['body']['message'] ?? '');
    }

    public function testLoginInvalidPassword(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertSame(401, $result['statusCode']);
        $this->assertFalse($result['body']['success'] ?? true);
        $this->assertSame('Invalid credentials', $result['body']['message'] ?? '');
    }

    public function testLoginResponseStructure(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertArrayHasKey('success', $result['body']);
        $this->assertArrayHasKey('message', $result['body']);
        $this->assertArrayHasKey('data', $result['body']);
    }

    public function testLoginStatusCodeCorrect(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame(200, $result['statusCode']);
    }

    public function testLoginValidationError(): void
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $this->assertSame(422, $result['statusCode']);
        $this->assertFalse($result['body']['success'] ?? true);
        $this->assertArrayHasKey('errors', $result['body']);
    }

    public function testLogoutSuccess(): void
    {
        $this->request('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $result = $this->request('POST', '/api/auth/logout');

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
        $this->assertSame('Logout successful', $result['body']['message'] ?? '');
    }

    public function testLogoutWhenNotLoggedIn(): void
    {
        $result = $this->request('POST', '/api/auth/logout');

        $this->assertSame(200, $result['statusCode']);
        $this->assertTrue($result['body']['success'] ?? false);
    }
}
