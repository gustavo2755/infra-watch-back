<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Contracts\AuthServiceInterface;
use App\Contracts\TokenServiceInterface;
use App\Requests\LoginRequest;
use App\Resources\SuccessResource;

/**
 * Handles authentication endpoints.
 */
final class AuthController
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly TokenServiceInterface $tokenService,
        private readonly LoginRequest $loginRequest
    ) {
    }

    /**
     * Authenticates user with email and password, returns JWT token.
     */
    public function login(Request $request): void
    {
        $data = $this->loginRequest->validate($request->body);

        $user = $this->authService->authenticate($data);

        $token = $this->tokenService->generate((int) $user->getId());

        Response::json(SuccessResource::make('Login successful', [
            'token' => $token,
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
        ]));
    }

    /**
     * Logout - client discards token. Returns success for consistency.
     */
    public function logout(Request $request): void
    {
        Response::json(SuccessResource::make('Logout successful'));
    }
}
