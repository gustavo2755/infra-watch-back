<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @param array{email: string, password: string} $credentials
     */
    public function authenticate(array $credentials): User;
}
