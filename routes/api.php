<?php

declare(strict_types=1);

use App\Http\AuthMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServiceCheckController;

/**
 * @return array<int, array{method: string, path: string, handler: callable, middleware?: callable}>
 */
return function (App\Container $container): array {
    $auth = new AuthMiddleware($container->getTokenService());
    $authController = new AuthController(
        $container->getAuthService(),
        $container->getTokenService(),
        new App\Requests\LoginRequest()
    );
    $serverController = new ServerController(
        $container->getServerService(),
        new App\Requests\StoreServerRequest(),
        new App\Requests\UpdateServerRequest()
    );
    $serviceCheckController = new ServiceCheckController(
        $container->getServiceCheckService(),
        new App\Requests\StoreServiceCheckRequest(),
        new App\Requests\UpdateServiceCheckRequest()
    );

    return [
        ['method' => 'POST', 'path' => '/api/auth/login', 'handler' => $authController->login(...)],
        ['method' => 'POST', 'path' => '/api/auth/logout', 'handler' => $authController->logout(...)],

        ['method' => 'POST', 'path' => '/api/servers', 'handler' => $serverController->create(...), 'middleware' => $auth],
        ['method' => 'PUT', 'path' => '/api/servers/{id}', 'handler' => $serverController->update(...), 'middleware' => $auth],
        ['method' => 'GET', 'path' => '/api/servers/{id}', 'handler' => $serverController->show(...), 'middleware' => $auth],
        ['method' => 'GET', 'path' => '/api/servers', 'handler' => $serverController->list(...), 'middleware' => $auth],

        ['method' => 'POST', 'path' => '/api/service-checks', 'handler' => $serviceCheckController->create(...), 'middleware' => $auth],
        ['method' => 'PUT', 'path' => '/api/service-checks/{id}', 'handler' => $serviceCheckController->update(...), 'middleware' => $auth],
        ['method' => 'GET', 'path' => '/api/service-checks/{id}', 'handler' => $serviceCheckController->show(...), 'middleware' => $auth],
        ['method' => 'GET', 'path' => '/api/service-checks/slug/{slug}', 'handler' => $serviceCheckController->showBySlug(...), 'middleware' => $auth],
        ['method' => 'GET', 'path' => '/api/service-checks', 'handler' => $serviceCheckController->list(...), 'middleware' => $auth],
        ['method' => 'POST', 'path' => '/api/servers/{serverId}/service-checks/{serviceCheckId}', 'handler' => $serviceCheckController->attachToServer(...), 'middleware' => $auth],
    ];
};
