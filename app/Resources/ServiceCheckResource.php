<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\ServiceCheck;

/**
 * Transforms a ServiceCheck model into API output.
 */
final class ServiceCheckResource extends BaseResource
{
    public function __construct(
        private readonly ServiceCheck $serviceCheck
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->serviceCheck->getId(),
            'name' => $this->serviceCheck->getName(),
            'slug' => $this->serviceCheck->getSlug(),
            'description' => $this->serviceCheck->getDescription(),
            'created_at' => $this->serviceCheck->getCreatedAt(),
            'updated_at' => $this->serviceCheck->getUpdatedAt(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function make(ServiceCheck $serviceCheck): array
    {
        return (new self($serviceCheck))->toArray();
    }

    /**
     * @param list<ServiceCheck> $serviceChecks
     * @return array<string, mixed>
     */
    public static function collection(array $serviceChecks, ?array $meta = null): array
    {
        $data = array_map(fn (ServiceCheck $sc) => self::make($sc), $serviceChecks);

        return [
            'data' => $data,
            'count' => count($data),
            'meta' => $meta,
        ];
    }
}
