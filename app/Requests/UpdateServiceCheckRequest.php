<?php

declare(strict_types=1);

namespace App\Requests;

use App\Exceptions\ValidationException;

/**
 * Request validation for updating a service check (partial updates allowed).
 */
final class UpdateServiceCheckRequest
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $errors = [];
        $validated = [];

        if (array_key_exists('name', $data)) {
            $val = $data['name'];
            if ($val === null || $val === '') {
                $errors['name'] = ['Name is required'];
            } elseif (!is_string($val)) {
                $errors['name'] = ['Name must be a string'];
            } else {
                $validated['name'] = $val;
            }
        }

        if (array_key_exists('slug', $data)) {
            $val = $data['slug'];
            if ($val === null || $val === '') {
                $errors['slug'] = ['Slug is required'];
            } elseif (!is_string($val)) {
                $errors['slug'] = ['Slug must be a string'];
            } else {
                $validated['slug'] = $val;
            }
        }

        if (array_key_exists('description', $data)) {
            $val = $data['description'];
            if ($val !== null && $val !== '') {
                if (!is_string($val)) {
                    $errors['description'] = ['Description must be a string'];
                } else {
                    $validated['description'] = $val;
                }
            } else {
                $validated['description'] = null;
            }
        }

        if ($errors !== []) {
            throw new ValidationException('Validation failed', $errors);
        }

        return $validated;
    }
}
