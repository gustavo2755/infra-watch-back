<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Requests\StoreServerRequest;
use App\Requests\UpdateServerRequest;
use App\Contracts\ServerServiceInterface;
use App\Contracts\ServiceCheckServiceInterface;
use App\Resources\ServerCollectionResource;
use App\Resources\ServerResource;
use App\Resources\SuccessResource;

/**
 * Handles server endpoints.
 */
final class ServerController
{
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly ServerServiceInterface $serverService,
        private readonly ServiceCheckServiceInterface $serviceCheckService,
        private readonly StoreServerRequest $storeRequest,
        private readonly UpdateServerRequest $updateRequest
    ) {
    }

    public function create(Request $request): void
    {
        $data = $this->storeRequest->validate($request->body);
        $userId = $request->getUserId();
        if ($userId !== null) {
            $data['created_by'] = $userId;
        }

        $server = $this->serverService->create($data);

        Response::json(SuccessResource::make('Server created', ServerResource::make($server)), 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $data = $this->updateRequest->validate($request->body);

        $server = $this->serverService->update($id, $data);
        $serviceChecks = $this->serviceCheckService->listByServerId($id);

        Response::json(SuccessResource::make('Server updated', ServerResource::make($server, $serviceChecks)));
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');

        $server = $this->serverService->findById($id);
        $serviceChecks = $this->serviceCheckService->listByServerId($id);

        Response::json(SuccessResource::make('Server retrieved', ServerResource::make($server, $serviceChecks)));
    }

    public function list(Request $request): void
    {
        $name = $request->getQuery('name');
        $isActive = $request->getQuery('is_active');
        [$page, $perPage] = $this->resolvePagination($request);

        if ($name !== null && $name !== '') {
            $result = $this->serverService->filterByNamePaginated($name, $page, $perPage);
        } elseif ($isActive !== null && $isActive !== '') {
            $result = $this->serverService->filterByIsActivePaginated(
                in_array($isActive, ['1', 'true', 'on'], true),
                $page,
                $perPage
            );
        } else {
            $result = $this->serverService->listPaginated($page, $perPage);
        }

        $meta = $this->buildPaginationMeta($page, $perPage, $result['total']);
        Response::json(
            SuccessResource::make(
                'Servers retrieved',
                ServerCollectionResource::makePaginated($result['items'], $meta, $this->serviceCheckService)
            )
        );
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->getParam('id');

        $this->serverService->delete($id);

        Response::json(SuccessResource::make('Server deleted', null));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePagination(Request $request): array
    {
        $page = max(1, (int) ($request->getQuery('page') ?? '1'));
        $perPage = max(1, (int) ($request->getQuery('per_page') ?? (string) self::DEFAULT_PER_PAGE));
        $perPage = min($perPage, self::MAX_PER_PAGE);

        return [$page, $perPage];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPaginationMeta(int $page, int $perPage, int $total): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
        ];
    }
}
