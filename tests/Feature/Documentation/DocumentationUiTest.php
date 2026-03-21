<?php

declare(strict_types=1);

namespace Tests\Documentation;

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Resources\ErrorResource;
use PHPUnit\Framework\TestCase;

final class DocumentationUiTest extends TestCase
{
    /**
     * @return array{statusCode: int, body: string}
     */
    private function requestWeb(string $method, string $path): array
    {
        $request = new Request($method, $path, [], [], [], []);
        $routes = (require __DIR__ . '/../../../routes/web.php')();
        $router = new Router($routes);

        ob_start();

        try {
            $router->dispatch($request);
        } catch (\Throwable $e) {
            Response::json(ErrorResource::make($e->getMessage()), 500);
        }

        $output = ob_get_clean();
        $statusCode = http_response_code() ?: 200;

        return [
            'statusCode' => $statusCode,
            'body' => $output !== false ? $output : '',
        ];
    }

    public function testDocsRouteReturnsHtml(): void
    {
        $result = $this->requestWeb('GET', '/docs');

        $this->assertSame(200, $result['statusCode']);
        $this->assertStringContainsString('swagger', $result['body']);
        $this->assertStringContainsString('<!DOCTYPE html>', $result['body']);
    }

    public function testDocsPageReferencesOpenApiJson(): void
    {
        $result = $this->requestWeb('GET', '/docs');

        $this->assertSame(200, $result['statusCode']);
        $this->assertStringContainsString('openapi.json', $result['body']);
    }
}
