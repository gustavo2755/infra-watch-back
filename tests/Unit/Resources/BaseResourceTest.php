<?php

declare(strict_types=1);

namespace Tests\Resources;

use App\Models\Server;
use App\Models\ServiceCheck;
use App\Resources\ErrorResource;
use App\Resources\ServerCollectionResource;
use App\Resources\ServerResource;
use App\Resources\ServiceCheckResource;
use App\Resources\SuccessResource;
use PHPUnit\Framework\TestCase;

final class BaseResourceTest extends TestCase
{
    public function testAllResourcesExtendBaseResource(): void
    {
        $this->assertInstanceOf(\App\Resources\BaseResource::class, new SuccessResource('OK'));
        $this->assertInstanceOf(\App\Resources\BaseResource::class, new ErrorResource('Error'));
        $this->assertInstanceOf(\App\Resources\BaseResource::class, new ServerResource(new Server(1, 'Test', null, '1.1.1.1')));
        $this->assertInstanceOf(\App\Resources\BaseResource::class, new ServerCollectionResource([]));
        $this->assertInstanceOf(\App\Resources\BaseResource::class, new ServiceCheckResource(new ServiceCheck(1, 'Test', 'test', null)));
    }

    public function testResourcesImplementToArray(): void
    {
        $success = new SuccessResource('OK', ['id' => 1]);
        $this->assertIsArray($success->toArray());
        $this->assertArrayHasKey('success', $success->toArray());

        $error = new ErrorResource('Fail', ['field' => ['Required']]);
        $this->assertIsArray($error->toArray());
        $this->assertArrayHasKey('errors', $error->toArray());

        $server = new Server(1, 'S', null, '1.1.1.1');
        $serverResource = new ServerResource($server);
        $this->assertIsArray($serverResource->toArray());
        $this->assertArrayHasKey('id', $serverResource->toArray());

        $collection = new ServerCollectionResource([$server]);
        $this->assertIsArray($collection->toArray());
        $this->assertArrayHasKey('data', $collection->toArray());

        $serviceCheck = new ServiceCheck(1, 'N', 'n', null);
        $serviceCheckResource = new ServiceCheckResource($serviceCheck);
        $this->assertIsArray($serviceCheckResource->toArray());
        $this->assertArrayHasKey('slug', $serviceCheckResource->toArray());
    }
}
