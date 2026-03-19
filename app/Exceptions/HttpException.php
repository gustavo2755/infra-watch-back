<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception for HTTP errors (404, 401, 403, etc.).
 * The status code is used by the handler to build the response.
 */
final class HttpException extends Exception
{
    public function __construct(
        string $message = 'Error',
        private readonly int $statusCode = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
