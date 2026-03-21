<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Resources\SuccessResource;
use PHPUnit\Framework\TestCase;

final class SuccessResourceTest extends TestCase
{
    public function testStructureHasSuccess(): void
    {
        $result = SuccessResource::make('OK');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testStructureHasMessage(): void
    {
        $result = SuccessResource::make('Operation completed');

        $this->assertArrayHasKey('message', $result);
        $this->assertSame('Operation completed', $result['message']);
    }

    public function testStructureHasData(): void
    {
        $result = SuccessResource::make('OK');

        $this->assertArrayHasKey('data', $result);
    }

    public function testDataIsNullWhenNotProvided(): void
    {
        $result = SuccessResource::make('OK');

        $this->assertNull($result['data']);
    }

    public function testDataContainsPayloadWhenProvided(): void
    {
        $payload = ['id' => 1, 'name' => 'Test'];
        $result = SuccessResource::make('Created', $payload);

        $this->assertSame($payload, $result['data']);
    }

    public function testCorrectSuccessStructure(): void
    {
        $result = SuccessResource::make('Success message', ['key' => 'value']);

        $this->assertSame(true, $result['success']);
        $this->assertSame('Success message', $result['message']);
        $this->assertSame(['key' => 'value'], $result['data']);
    }

    public function testDefaultMessage(): void
    {
        $result = SuccessResource::make();

        $this->assertSame('Success', $result['message']);
    }
}
