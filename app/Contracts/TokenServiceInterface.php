<?php

declare(strict_types=1);

namespace App\Contracts;

interface TokenServiceInterface
{
    public function generate(int $userId): string;

    public function validate(string $token): int;
}
