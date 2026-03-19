<?php

declare(strict_types=1);

namespace Tests\Requests;

use App\Exceptions\ValidationException;
use App\Requests\StoreServiceCheckRequest;
use PHPUnit\Framework\TestCase;

final class StoreServiceCheckRequestTest extends TestCase
{
    private StoreServiceCheckRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreServiceCheckRequest();
    }

    public function testValidPayload(): void
    {
        $data = ['name' => 'Nginx', 'slug' => 'nginx', 'description' => 'Web server'];
        $result = $this->request->validate($data);
        $this->assertSame('Nginx', $result['name']);
        $this->assertSame('nginx', $result['slug']);
        $this->assertSame('Web server', $result['description']);
    }

    public function testNameAbsent(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['slug' => 'nginx']);
    }

    public function testSlugAbsent(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['name' => 'Nginx']);
    }

    public function testInvalidTypes(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['name' => 123, 'slug' => ['a']]);
    }

    public function testInvalidDescription(): void
    {
        $this->expectException(ValidationException::class);
        $this->request->validate(['name' => 'Nginx', 'slug' => 'nginx', 'description' => 456]);
    }

    public function testValidWithoutDescription(): void
    {
        $data = ['name' => 'Nginx', 'slug' => 'nginx'];
        $result = $this->request->validate($data);
        $this->assertNull($result['description']);
    }
}
