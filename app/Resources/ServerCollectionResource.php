<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Server;

/**
 * Transforms a list of servers into API output.
 */
final class ServerCollectionResource extends BaseResource
{
    /**
     * @param list<Server> $servers
     */
    public function __construct(
        private readonly array $servers
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_map(fn (Server $s) => ServerResource::make($s), $this->servers);

        return [
            'data' => $data,
            'count' => count($data),
        ];
    }

    /**
     * @param list<Server> $servers
     * @return array<string, mixed>
     */
    public static function make(array $servers): array
    {
        return (new self($servers))->toArray();
    }
}
