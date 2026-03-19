<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

final class ExceptionsTest extends TestCase
{
    public function testValidationExceptionInstantiation(): void
    {
        $e = new ValidationException('Custom message', ['field' => ['Error 1']]);

        $this->assertSame('Custom message', $e->getMessage());
        $this->assertSame(['field' => ['Error 1']], $e->getErrors());
    }

    public function testValidationExceptionDefaultMessage(): void
    {
        $e = new ValidationException();
        $this->assertSame('Validation failed', $e->getMessage());
        $this->assertSame([], $e->getErrors());
    }

    public function testHttpExceptionNotFound(): void
    {
        $e = new HttpException('User not found', 404);

        $this->assertSame('User not found', $e->getMessage());
        $this->assertSame(404, $e->getStatusCode());
    }

    public function testHttpExceptionUnauthorized(): void
    {
        $e = new HttpException('Invalid credentials', 401);

        $this->assertSame('Invalid credentials', $e->getMessage());
        $this->assertSame(401, $e->getStatusCode());
    }

    public function testHttpExceptionForbidden(): void
    {
        $e = new HttpException('Access denied', 403);

        $this->assertSame('Access denied', $e->getMessage());
        $this->assertSame(403, $e->getStatusCode());
    }

    public function testHttpExceptionDefault(): void
    {
        $e = new HttpException();

        $this->assertSame('Error', $e->getMessage());
        $this->assertSame(500, $e->getStatusCode());
    }

    public function testValidationExceptionIsThrowable(): void
    {
        $e = new ValidationException('Test');

        $this->assertInstanceOf(\Throwable::class, $e);
    }

    public function testHttpExceptionIsThrowable(): void
    {
        $e = new HttpException('Test', 404);

        $this->assertInstanceOf(\Throwable::class, $e);
    }
}
