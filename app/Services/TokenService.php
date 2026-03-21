<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenServiceInterface;
use App\Exceptions\HttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Generates and validates JWT tokens for API authentication.
 */
final class TokenService implements TokenServiceInterface
{
    private const ALGORITHM = 'HS256';
    private const DEFAULT_TTL_HOURS = 24;

    public function __construct(
        private readonly string $secret,
        private readonly int $ttlHours = self::DEFAULT_TTL_HOURS
    ) {
    }

    public function generate(int $userId): string
    {
        $now = time();
        $payload = [
            'user_id' => $userId,
            'iat' => $now,
            'exp' => $now + ($this->ttlHours * 3600),
        ];

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * @throws HttpException 401 when token is invalid or expired
     */
    public function validate(string $token): int
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
            $userId = $decoded->user_id ?? null;

            if ($userId === null || !is_numeric($userId)) {
                throw new HttpException('Invalid token', 401);
            }

            return (int) $userId;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new HttpException('Token expired', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new HttpException('Invalid token', 401);
        } catch (\Throwable $e) {
            throw new HttpException('Invalid token', 401);
        }
    }
}
