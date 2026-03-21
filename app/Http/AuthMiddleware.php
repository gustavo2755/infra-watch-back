<?php

declare(strict_types=1);

namespace App\Http;

use App\Contracts\TokenServiceInterface;
use App\Exceptions\HttpException;

/**
 * Protects routes that require authentication via Bearer token.
 */
final class AuthMiddleware
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService
    ) {
    }

    public function __invoke(Request $request, callable $next): void
    {
        $auth = $request->getHeader('Authorization');

        if ($auth === null || $auth === '') {
            throw new HttpException('Unauthorized', 401);
        }

        if (!str_starts_with($auth, 'Bearer ')) {
            throw new HttpException('Invalid authorization header', 401);
        }

        $token = substr($auth, 7);
        $userId = $this->tokenService->validate($token);

        $requestWithUser = new Request(
            $request->method,
            $request->path,
            $request->query,
            $request->body,
            $request->params,
            $request->headers,
            $userId
        );
        $next($requestWithUser);
    }
}
