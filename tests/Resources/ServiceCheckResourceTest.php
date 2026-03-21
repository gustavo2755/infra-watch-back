<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\ServiceCheck;
use App\Resources\ServiceCheckResource;
use PHPUnit\Framework\TestCase;

final class ServiceCheckResourceTest extends TestCase
{
    public function testTransformsSingleItem(): void
    {
        $serviceCheck = new ServiceCheck(
            1,
            'Nginx',
            'nginx',
            'Web server process',
            '2025-01-01 10:00:00',
            '2025-01-01 12:00:00'
        );

        $result = ServiceCheckResource::make($serviceCheck);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('Nginx', $result['name']);
        $this->assertSame('nginx', $result['slug']);
        $this->assertSame('Web server process', $result['description']);
        $this->assertSame('2025-01-01 10:00:00', $result['created_at']);
        $this->assertSame('2025-01-01 12:00:00', $result['updated_at']);
    }

    public function testExposesCorrectFields(): void
    {
        $serviceCheck = new ServiceCheck(1, 'Test', 'test', null);
        $result = ServiceCheckResource::make($serviceCheck);

        $expectedKeys = ['id', 'name', 'slug', 'description', 'created_at', 'updated_at'];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }

        $this->assertCount(count($expectedKeys), $result);
    }

    public function testConsistentStructure(): void
    {
        $serviceCheck = new ServiceCheck(5, 'PHP-FPM', 'php-fpm', 'PHP process manager', '2025-01-01', '2025-01-02');
        $result = ServiceCheckResource::make($serviceCheck);

        $this->assertSame(5, $result['id']);
        $this->assertSame('PHP-FPM', $result['name']);
        $this->assertSame('php-fpm', $result['slug']);
        $this->assertSame('PHP process manager', $result['description']);
        $this->assertSame('2025-01-01', $result['created_at']);
        $this->assertSame('2025-01-02', $result['updated_at']);
    }

    public function testCollection(): void
    {
        $items = [
            new ServiceCheck(1, 'Nginx', 'nginx', null),
            new ServiceCheck(2, 'MySQL', 'mysql', null),
        ];

        $result = ServiceCheckResource::collection($items);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['count']);
        $this->assertSame('nginx', $result['data'][0]['slug']);
        $this->assertSame('mysql', $result['data'][1]['slug']);
    }

    public function testCollectionEmpty(): void
    {
        $result = ServiceCheckResource::collection([]);

        $this->assertSame([], $result['data']);
        $this->assertSame(0, $result['count']);
    }
}
