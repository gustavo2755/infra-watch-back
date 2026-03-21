<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Resources\ErrorResource;
use PHPUnit\Framework\TestCase;

final class ErrorResourceTest extends TestCase
{
    public function testStructureHasSuccess(): void
    {
        $result = ErrorResource::make('Error');

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
    }

    public function testStructureHasMessage(): void
    {
        $result = ErrorResource::make('Something went wrong');

        $this->assertArrayHasKey('message', $result);
        $this->assertSame('Something went wrong', $result['message']);
    }

    public function testStructureHasErrors(): void
    {
        $result = ErrorResource::make('Error');

        $this->assertArrayHasKey('errors', $result);
    }

    public function testErrorsEmptyWhenNotProvided(): void
    {
        $result = ErrorResource::make('Error');

        $this->assertSame([], $result['errors']);
    }

    public function testErrorsContainValidationErrors(): void
    {
        $errors = ['email' => ['Invalid format'], 'name' => ['Required']];
        $result = ErrorResource::make('Validation failed', $errors);

        $this->assertSame($errors, $result['errors']);
    }

    public function testCorrectErrorStructure(): void
    {
        $result = ErrorResource::make('Bad request', ['field' => ['Error 1']]);

        $this->assertSame(false, $result['success']);
        $this->assertSame('Bad request', $result['message']);
        $this->assertSame(['field' => ['Error 1']], $result['errors']);
    }

    public function testFromValidationException(): void
    {
        $e = new ValidationException('Validation failed', ['name' => ['Name is required']]);
        $result = ErrorResource::fromValidationException($e);

        $this->assertFalse($result['success']);
        $this->assertSame('Validation failed', $result['message']);
        $this->assertSame(['name' => ['Name is required']], $result['errors']);
    }

    public function testFromHttpException(): void
    {
        $e = new HttpException('Not found', 404);
        $result = ErrorResource::fromHttpException($e);

        $this->assertFalse($result['success']);
        $this->assertSame('Not found', $result['message']);
        $this->assertSame([], $result['errors']);
    }

    public function testFromThrowableWithValidationException(): void
    {
        $e = new ValidationException('Invalid input', ['slug' => ['Already exists']]);
        $result = ErrorResource::fromThrowable($e);

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid input', $result['message']);
        $this->assertSame(['slug' => ['Already exists']], $result['errors']);
    }

    public function testFromThrowableWithHttpException(): void
    {
        $e = new HttpException('Unauthorized', 401);
        $result = ErrorResource::fromThrowable($e);

        $this->assertFalse($result['success']);
        $this->assertSame('Unauthorized', $result['message']);
        $this->assertSame([], $result['errors']);
    }

    public function testFromThrowableWithGenericException(): void
    {
        $e = new \RuntimeException('Unexpected error');
        $result = ErrorResource::fromThrowable($e);

        $this->assertFalse($result['success']);
        $this->assertSame('Unexpected error', $result['message']);
        $this->assertSame([], $result['errors']);
    }
}
