<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\Server;
use App\Resources\ServerCollectionResource;
use App\Resources\ServerResource;
use PHPUnit\Framework\TestCase;

final class ServerCollectionResourceTest extends TestCase
{
    public function testTransformsCollection(): void
    {
        $servers = [
            new Server(1, 'Server A', null, '1.1.1.1'),
            new Server(2, 'Server B', null, '2.2.2.2'),
        ];

        $result = ServerCollectionResource::make($servers);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['count']);
    }

    public function testConsistencyWithSingleItem(): void
    {
        $server = new Server(99, 'Single', 'Desc', '9.9.9.9');
        $servers = [$server];

        $collectionResult = ServerCollectionResource::make($servers);
        $singleResult = ServerResource::make($server);

        $this->assertCount(1, $collectionResult['data']);
        $this->assertSame($singleResult, $collectionResult['data'][0]);
    }

    public function testEmptyList(): void
    {
        $result = ServerCollectionResource::make([]);

        $this->assertSame([], $result['data']);
        $this->assertSame(0, $result['count']);
    }

    public function testListStructure(): void
    {
        $servers = [
            new Server(1, 'A', null, '1.1.1.1'),
            new Server(2, 'B', null, '2.2.2.2'),
        ];

        $result = ServerCollectionResource::make($servers);

        $this->assertSame(1, $result['data'][0]['id']);
        $this->assertSame('A', $result['data'][0]['name']);
        $this->assertSame(2, $result['data'][1]['id']);
        $this->assertSame('B', $result['data'][1]['name']);
    }
}
