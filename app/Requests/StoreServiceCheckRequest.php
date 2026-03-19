<?php

declare(strict_types=1);

namespace App\Requests;

use App\Exceptions\ValidationException;

/**
 * Request validation for creating a service check.
 */
final class StoreServiceCheckRequest
{
    /**
     * @param array<string, mixed> $data
     * @return array{name: string, slug: string, description: string|null}
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $errors = [];
        $validated = [];

        $name = $data['name'] ?? null;

        if ($name === null || $name === '') {
            $errors['name'] = ['Name is required'];
        } elseif (!is_string($name)) {
            $errors['name'] = ['Name must be a string'];
        } else {
            $validated['name'] = $name;
        }

        $slug = $data['slug'] ?? null;

        if ($slug === null || $slug === '') {
            $errors['slug'] = ['Slug is required'];
        } elseif (!is_string($slug)) {
            $errors['slug'] = ['Slug must be a string'];
        } else {
            $validated['slug'] = $slug;
        }

        $description = $data['description'] ?? null;

        if ($description !== null && $description !== '') {
            if (!is_string($description)) {
                $errors['description'] = ['Description must be a string'];
            } else {
                $validated['description'] = $description;
            }
        } else {
            $validated['description'] = null;
        }

        if ($errors !== []) {
            throw new ValidationException('Validation failed', $errors);
        }

        return $validated;
    }
}
