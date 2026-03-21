<?php

declare(strict_types=1);

namespace App\Resources;

/**
 * Standardizes success responses.
 *
 * Structure: success, message, data
 */
final class SuccessResource extends BaseResource
{
    public function __construct(
        private readonly string $message = 'Success',
        private readonly mixed $data = null
    ) {
    }

    /**
     * @return array{success: bool, message: string, data: mixed}
     */
    public function toArray(): array
    {
        return [
            'success' => true,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    /**
     * @return array{success: bool, message: string, data: mixed}
     */
    public static function make(string $message = 'Success', mixed $data = null): array
    {
        return (new self($message, $data))->toArray();
    }
}
