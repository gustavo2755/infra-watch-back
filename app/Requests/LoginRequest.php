<?php

declare(strict_types=1);

namespace App\Requests;

use App\Exceptions\ValidationException;

/**
 * Request validation for login credentials.
 */
final class LoginRequest
{
    /**
     * @param array<string, mixed> $data
     * @return array{email: string, password: string}
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $errors = [];

        $email = $data['email'] ?? null;

        if ($email === null || $email === '') {
            $errors['email'] = ['Email is required'];
        } elseif (!is_string($email)) {
            $errors['email'] = ['Email must be a string'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Email format is invalid'];
        }

        $password = $data['password'] ?? null;

        if ($password === null || $password === '') {
            $errors['password'] = ['Password is required'];
        } elseif (!is_string($password)) {
            $errors['password'] = ['Password must be a string'];
        }

        if ($errors !== []) {
            throw new ValidationException('Validation failed', $errors);
        }

        return [
            'email' => (string) $email,
            'password' => (string) $password,
        ];
    }
}
