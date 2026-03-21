<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\TokenServiceInterface;
use App\Exceptions\HttpException;
use App\Services\TokenService;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

final class TokenServiceTest extends TestCase
{
    private const SECRET = 'test-secret-at-least-32-chars-for-hs256';

    private TokenServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TokenService(self::SECRET);
    }

    public function testGenerateReturnsValidToken(): void
    {
        $token = $this->service->generate(42);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateReturnsUserIdForValidToken(): void
    {
        $token = $this->service->generate(123);

        $userId = $this->service->validate($token);

        $this->assertSame(123, $userId);
    }

    public function testValidateThrowsWhenTokenExpired(): void
    {
        $now = time();
        $payload = [
            'user_id' => 1,
            'iat' => $now - 3600,
            'exp' => $now - 60,
        ];
        $token = JWT::encode($payload, self::SECRET, 'HS256');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Token expired');

        $this->service->validate($token);
    }

    public function testValidateThrowsWhenSignatureInvalid(): void
    {
        $token = $this->service->generate(1);

        $serviceWithWrongSecret = new TokenService('different-secret-at-least-32-chars-here');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid token');

        $serviceWithWrongSecret->validate($token);
    }

    public function testValidateThrowsWhenTokenMalformed(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->service->validate('not.a.valid.jwt');
    }

    public function testValidateThrowsWhenUserIdMissing(): void
    {
        $now = time();
        $payload = [
            'iat' => $now,
            'exp' => $now + 3600,
        ];
        $token = JWT::encode($payload, self::SECRET, 'HS256');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->service->validate($token);
    }

    public function testValidateThrowsWhenUserIdNotNumeric(): void
    {
        $now = time();
        $payload = [
            'user_id' => 'invalid',
            'iat' => $now,
            'exp' => $now + 3600,
        ];
        $token = JWT::encode($payload, self::SECRET, 'HS256');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->service->validate($token);
    }

    public function testValidateThrowsWhenTokenIsRandomString(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->service->validate('random-garbage-string');
    }
}
