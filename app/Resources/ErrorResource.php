<?php

declare(strict_types=1);

namespace App\Resources;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;

/**
 * Standardizes error responses.
 *
 * Structure: success, message, errors
 * Compatible with ValidationException, HttpException, and controlled failures.
 */
final class ErrorResource extends BaseResource
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(
        private readonly string $message = 'Error',
        private readonly array $errors = []
    ) {
    }

    /**
     * @return array{success: bool, message: string, errors: array<string, list<string>>}
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }

    /**
     * @param array<string, list<string>> $errors
     * @return array{success: bool, message: string, errors: array<string, list<string>>}
     */
    public static function make(string $message = 'Error', array $errors = []): array
    {
        return (new self($message, $errors))->toArray();
    }

    /**
     * @return array{success: bool, message: string, errors: array<string, list<string>>}
     */
    public static function fromValidationException(ValidationException $e): array
    {
        return self::make($e->getMessage(), $e->getErrors());
    }

    /**
     * @return array{success: bool, message: string, errors: array<string, list<string>>}
     */
    public static function fromHttpException(HttpException $e): array
    {
        return self::make($e->getMessage(), []);
    }

    /**
     * @return array{success: bool, message: string, errors: array<string, list<string>>}
     */
    public static function fromThrowable(\Throwable $e): array
    {
        if ($e instanceof ValidationException) {
            return self::fromValidationException($e);
        }

        if ($e instanceof HttpException) {
            return self::fromHttpException($e);
        }

        return self::make($e->getMessage(), []);
    }
}
