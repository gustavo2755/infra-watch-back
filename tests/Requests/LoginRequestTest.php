<?php

declare(strict_types=1);

namespace Tests\Requests;

use App\Exceptions\ValidationException;
use App\Requests\LoginRequest;
use PHPUnit\Framework\TestCase;

final class LoginRequestTest extends TestCase
{
    private LoginRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new LoginRequest();
    }

    public function testValidPayload(): void
    {
        $data = ['email' => 'user@example.com', 'password' => 'secret123'];

        $result = $this->request->validate($data);

        $this->assertSame('user@example.com', $result['email']);
        $this->assertSame('secret123', $result['password']);
    }

    public function testEmailAbsent(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['password' => 'secret']);
    }

    public function testEmailInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['email' => 'not-an-email', 'password' => 'secret']);
    }

    public function testPasswordAbsent(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['email' => 'user@example.com']);
    }

    public function testInvalidTypes(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['email' => 123, 'password' => ['array']]);
    }

    public function testEmailEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['email' => '', 'password' => 'secret']);
    }

    public function testPasswordEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['email' => 'user@example.com', 'password' => '']);
    }
}
