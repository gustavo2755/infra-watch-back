<?php

declare(strict_types=1);

namespace Tests\Requests;

use App\Exceptions\ValidationException;
use App\Requests\UpdateServerRequest;
use PHPUnit\Framework\TestCase;

final class UpdateServerRequestTest extends TestCase
{
    private UpdateServerRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateServerRequest();
    }

    public function testPartialPayloadValid(): void
    {
        $data = ['name' => 'Updated Name', 'is_active' => false];
        $result = $this->request->validate($data);
        $this->assertSame('Updated Name', $result['name']);
        $this->assertFalse($result['is_active']);
        $this->assertCount(2, $result);
    }

    public function testInvalidFieldType(): void
    {
        $data = ['name' => 123];
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidBoolean(): void
    {
        $data = ['is_active' => 'yes'];
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testInvalidInteger(): void
    {
        $data = ['check_interval_seconds' => 'sixty'];
        $this->expectException(ValidationException::class);
        $this->request->validate($data);
    }

    public function testEmptyPayloadValid(): void
    {
        $result = $this->request->validate([]);
        $this->assertSame([], $result);
    }

    public function testValidBooleanFromString(): void
    {
        $data = ['is_active' => '1'];
        $result = $this->request->validate($data);
        $this->assertTrue($result['is_active']);
    }
}
