<?php

declare(strict_types=1);

namespace App\Http;

use App\Resources\ErrorResource;

/**
 * Simple router for API routes.
 */
final class Router
{
    /**
     * @param array<int, array{method: string, path: string, handler: callable, middleware?: callable}> $routes
     */
    public function __construct(
        private readonly array $routes
    ) {
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            $params = $this->match($request->method, $request->path, $route['method'], $route['path']);

            if ($params !== null) {
                $requestWithParams = new Request(
                    $request->method,
                    $request->path,
                    $request->query,
                    $request->body,
                    $params,
                    $request->headers,
                    null
                );

                $handler = $route['handler'];
                $middleware = $route['middleware'] ?? null;

                if ($middleware !== null) {
                    $next = fn (Request $r) => $handler($r);
                    $middleware($requestWithParams, $next);
                } else {
                    $handler($requestWithParams);
                }

                return;
            }
        }

        Response::json(ErrorResource::make('Not found'), 404);
    }

    /**
     * @return array<string, string>|null Matched params or null
     */
    private function match(string $method, string $path, string $routeMethod, string $routePath): ?array
    {
        if ($method !== $routeMethod) {
            return null;
        }

        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches) === 1) {
            $params = [];

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            return $params;
        }

        return null;
    }
}
