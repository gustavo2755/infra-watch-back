<?php

declare(strict_types=1);

namespace App\Resources;

use App\Contracts\ServiceCheckServiceInterface;
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
        private readonly array $servers,
        private readonly ?ServiceCheckServiceInterface $serviceCheckService = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_map(function (Server $s): array {
            $serviceChecks = [];
            if ($this->serviceCheckService !== null && $s->getId() !== null) {
                $serviceChecks = $this->serviceCheckService->listByServerId((int) $s->getId());
            }

            return ServerResource::make($s, $serviceChecks);
        }, $this->servers);

        return [
            'data' => $data,
            'count' => count($data),
        ];
    }

    /**
     * @param list<Server> $servers
     * @return array<string, mixed>
     */
    public static function make(array $servers, ?ServiceCheckServiceInterface $serviceCheckService = null): array
    {
        return (new self($servers, $serviceCheckService))->toArray();
    }
}
