<?php

declare(strict_types=1);

$config = require __DIR__ . '/../bootstrap/app.php';

$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') ?: '/';

$query = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

$body = [];
$rawBody = file_get_contents('php://input');
if ($rawBody !== false && $rawBody !== '') {
    $decoded = json_decode($rawBody, true);
    $body = is_array($decoded) ? $decoded : [];
}

$headers = [];
if (function_exists('getallheaders')) {
    $headers = getallheaders() ?: [];
} else {
    foreach ($_SERVER ?? [] as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
            $name = str_replace('_', '-', substr($key, 5));
            $headers[$name] = $value;
        }
    }
}

$request = new App\Http\Request(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $path,
    $query,
    $body,
    [],
    $headers
);

$container = new App\Container($config);

try {
    if (str_starts_with($path, '/api')) {
        $routes = (require __DIR__ . '/../routes/api.php')($container);
        $router = new App\Http\Router($routes);
        $router->dispatch($request);
    } else {
        $routes = (require __DIR__ . '/../routes/web.php')();
        $router = new App\Http\Router($routes);
        $router->dispatch($request);
    }
} catch (App\Exceptions\ValidationException $e) {
    App\Http\Response::json(App\Resources\ErrorResource::fromValidationException($e), 422);
} catch (App\Exceptions\HttpException $e) {
    App\Http\Response::json(App\Resources\ErrorResource::fromHttpException($e), $e->getStatusCode());
} catch (Throwable $e) {
    App\Http\Response::json(App\Resources\ErrorResource::fromThrowable($e), 500);
}
