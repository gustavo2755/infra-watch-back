<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Simple HTTP request representation.
 */
final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $params = [],
        public readonly array $headers = [],
        public readonly ?int $userId = null
    ) {
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getHeader(string $name): ?string
    {
        $key = strtolower($name);
        foreach ($this->headers as $headerName => $value) {
            if (strtolower((string) $headerName) === $key) {
                return is_array($value) ? ($value[0] ?? null) : (string) $value;
            }
        }

        return null;
    }

    public function getQuery(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    public function getParam(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }
}
