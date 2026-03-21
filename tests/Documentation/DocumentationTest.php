<?php

declare(strict_types=1);

namespace Tests\Documentation;

use Tests\HttpTestCase;

final class DocumentationTest extends HttpTestCase
{
    public function testOpenApiJsonIsAccessible(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $this->assertIsArray($result['body']);
        $this->assertArrayHasKey('openapi', $result['body']);
        $this->assertArrayHasKey('paths', $result['body']);
    }

    public function testOpenApiJsonContainsOpenApiVersion(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame('3.0.3', $result['body']['openapi'] ?? null);
    }

    public function testOpenApiJsonContainsPart1Routes(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $paths = $result['body']['paths'] ?? [];

        $this->assertArrayHasKey('/api/auth/login', $paths);
        $this->assertArrayHasKey('/api/servers', $paths);
        $this->assertArrayHasKey('/api/service-checks', $paths);
        $this->assertArrayHasKey('/api/openapi.json', $paths);
    }

    public function testOpenApiJsonContainsBearerSecurity(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $schemes = $result['body']['components']['securitySchemes'] ?? [];

        $this->assertArrayHasKey('Bearer', $schemes);
        $this->assertSame('http', $schemes['Bearer']['type'] ?? null);
        $this->assertSame('bearer', $schemes['Bearer']['scheme'] ?? null);
        $this->assertSame('JWT', $schemes['Bearer']['bearerFormat'] ?? null);

        $this->assertArrayHasKey('security', $result['body']);
        $this->assertIsArray($result['body']['security']);
    }

    public function testOpenApiJsonContainsMainStatusCodes(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $loginResponses = $result['body']['paths']['/api/auth/login']['post']['responses'] ?? [];

        $this->assertArrayHasKey('200', $loginResponses);
        $this->assertArrayHasKey('401', $loginResponses);
        $this->assertArrayHasKey('422', $loginResponses);

        $createServerResponses = $result['body']['paths']['/api/servers']['post']['responses'] ?? [];
        $this->assertArrayHasKey('201', $createServerResponses);
        $this->assertArrayHasKey('401', $createServerResponses);

        $getServerResponses = $result['body']['paths']['/api/servers/{id}']['get']['responses'] ?? [];
        $this->assertArrayHasKey('404', $getServerResponses);
        $this->assertArrayHasKey('500', $getServerResponses);
    }

    public function testOpenApiJsonSchemasExist(): void
    {
        $result = $this->request('GET', '/api/openapi.json');

        $this->assertSame(200, $result['statusCode']);
        $schemas = $result['body']['components']['schemas'] ?? [];

        $this->assertArrayHasKey('LoginRequest', $schemas);
        $this->assertArrayHasKey('Server', $schemas);
        $this->assertArrayHasKey('ServiceCheck', $schemas);
        $this->assertArrayHasKey('ErrorResponse', $schemas);
        $this->assertArrayHasKey('SuccessResponse', $schemas);
    }
}
