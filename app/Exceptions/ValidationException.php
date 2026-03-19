<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception for input validation failures.
 * Carries a main message and optional field-level errors.
 */
final class ValidationException extends Exception
{
    /**
     * @param array<string, list<string>> $errors Field name => list of error messages
     */
    public function __construct(
        string $message = 'Validation failed',
        private readonly array $errors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
