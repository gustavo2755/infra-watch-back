<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Requests\StoreServiceCheckRequest;
use App\Requests\UpdateServiceCheckRequest;
use App\Contracts\ServiceCheckServiceInterface;
use App\Resources\ServiceCheckResource;
use App\Resources\SuccessResource;

/**
 * Handles service check endpoints.
 */
final class ServiceCheckController
{
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly ServiceCheckServiceInterface $serviceCheckService,
        private readonly StoreServiceCheckRequest $storeRequest,
        private readonly UpdateServiceCheckRequest $updateRequest
    ) {
    }

    public function create(Request $request): void
    {
        $data = $this->storeRequest->validate($request->body);

        $serviceCheck = $this->serviceCheckService->create($data);

        Response::json(SuccessResource::make('Service check created', ServiceCheckResource::make($serviceCheck)), 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $data = $this->updateRequest->validate($request->body);

        if ($data === []) {
            $serviceCheck = $this->serviceCheckService->findById($id);
        } else {
            $serviceCheck = $this->serviceCheckService->update($id, $data);
        }

        Response::json(SuccessResource::make('Service check updated', ServiceCheckResource::make($serviceCheck)));
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');

        $serviceCheck = $this->serviceCheckService->findById($id);

        Response::json(SuccessResource::make('Service check retrieved', ServiceCheckResource::make($serviceCheck)));
    }

    public function showBySlug(Request $request): void
    {
        $slug = $request->getParam('slug') ?? '';

        $serviceCheck = $this->serviceCheckService->findBySlug($slug);

        Response::json(SuccessResource::make('Service check retrieved', ServiceCheckResource::make($serviceCheck)));
    }

    public function list(Request $request): void
    {
        [$page, $perPage] = $this->resolvePagination($request);
        $result = $this->serviceCheckService->listPaginated($page, $perPage);
        $meta = $this->buildPaginationMeta($page, $perPage, $result['total']);

        Response::json(
            SuccessResource::make('Service checks retrieved', ServiceCheckResource::collection($result['items'], $meta))
        );
    }

    public function listAvailableByServer(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');
        [$page, $perPage] = $this->resolvePagination($request);
        $result = $this->serviceCheckService->listAvailableByServerIdPaginated($serverId, $page, $perPage);
        $meta = $this->buildPaginationMeta($page, $perPage, $result['total']);

        Response::json(
            SuccessResource::make(
                'Available service checks retrieved',
                ServiceCheckResource::collection($result['items'], $meta)
            )
        );
    }

    public function attachToServer(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');
        $serviceCheckId = (int) $request->getParam('serviceCheckId');

        $this->serviceCheckService->attachToServer($serverId, $serviceCheckId);

        Response::json(SuccessResource::make('Service check linked to server'));
    }

    public function detachFromServer(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');
        $serviceCheckId = (int) $request->getParam('serviceCheckId');

        $this->serviceCheckService->detachFromServer($serverId, $serviceCheckId);

        Response::json(SuccessResource::make('Service check unlinked successfully', null));
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->getParam('id');

        $this->serviceCheckService->delete($id);

        Response::json(SuccessResource::make('Service check deleted', null));
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
