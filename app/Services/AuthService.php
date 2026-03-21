<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Exceptions\HttpException;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Service for authentication.
 */
final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Authenticates user with validated credentials.
     *
     * @param array{email: string, password: string} $credentials
     * @throws HttpException 401 when email not found or password invalid
     */
    public function authenticate(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if ($user === null) {
            throw new HttpException('Invalid credentials', 401);
        }

        if (!password_verify($credentials['password'], $user->getPassword() ?? '')) {
            throw new HttpException('Invalid credentials', 401);
        }

        return $user;
    }
}
