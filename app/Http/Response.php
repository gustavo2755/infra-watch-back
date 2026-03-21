<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Simple HTTP response handler.
 */
final class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}
