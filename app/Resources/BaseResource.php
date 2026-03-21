<?php

declare(strict_types=1);

namespace App\Resources;

/**
 * Base structure for API response resources.
 */
abstract class BaseResource
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
