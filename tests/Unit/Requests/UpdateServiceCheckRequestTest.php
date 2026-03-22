<?php

declare(strict_types=1);

namespace Tests\Requests;

use App\Exceptions\ValidationException;
use App\Requests\UpdateServiceCheckRequest;
use PHPUnit\Framework\TestCase;

final class UpdateServiceCheckRequestTest extends TestCase
{
    private UpdateServiceCheckRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateServiceCheckRequest();
    }

    public function testEmptyPayloadReturnsEmptyArray(): void
    {
        $result = $this->request->validate([]);

        $this->assertSame([], $result);
    }

    public function testPartialUpdateNameOnly(): void
    {
        $result = $this->request->validate(['name' => 'New Name']);

        $this->assertSame('New Name', $result['name']);
        $this->assertCount(1, $result);
    }

    public function testPartialUpdateSlugOnly(): void
    {
        $result = $this->request->validate(['slug' => 'new-slug']);

        $this->assertSame('new-slug', $result['slug']);
        $this->assertCount(1, $result);
    }

    public function testPartialUpdateDescriptionOnly(): void
    {
        $result = $this->request->validate(['description' => 'New description']);

        $this->assertSame('New description', $result['description']);
        $this->assertCount(1, $result);
    }

    public function testNamePresentButEmptyThrows(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->request->validate(['name' => '']);
    }

    public function testNamePresentButNullThrows(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->validate(['name' => null]);
    }

    public function testSlugPresentButEmptyThrows(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->request->validate(['slug' => '']);
    }

    public function testSlugPresentButNullThrows(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->validate(['slug' => null]);
    }

    public function testNameNotStringThrows(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->validate(['name' => 123]);
    }

    public function testSlugNotStringThrows(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->validate(['slug' => ['array']]);
    }

    public function testDescriptionNotStringThrows(): void
    {
        $this->expectException(ValidationException::class);

        $this->request->validate(['description' => 456]);
    }

    public function testDescriptionNullValidatedAsNull(): void
    {
        $result = $this->request->validate(['description' => null]);

        $this->assertArrayHasKey('description', $result);
        $this->assertNull($result['description']);
    }

    public function testDescriptionEmptyStringValidatedAsNull(): void
    {
        $result = $this->request->validate(['description' => '']);

        $this->assertArrayHasKey('description', $result);
        $this->assertNull($result['description']);
    }

    public function testMultipleFieldsValid(): void
    {
        $result = $this->request->validate([
            'name' => 'Redis',
            'slug' => 'redis',
            'description' => 'Cache server',
        ]);

        $this->assertSame('Redis', $result['name']);
        $this->assertSame('redis', $result['slug']);
        $this->assertSame('Cache server', $result['description']);
        $this->assertCount(3, $result);
    }
}
