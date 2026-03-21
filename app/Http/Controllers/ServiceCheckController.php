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
        $serviceChecks = $this->serviceCheckService->list();

        Response::json(SuccessResource::make('Service checks retrieved', ServiceCheckResource::collection($serviceChecks)));
    }

    public function listAvailableByServer(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');

        $serviceChecks = $this->serviceCheckService->listAvailableByServerId($serverId);

        Response::json(SuccessResource::make('Available service checks retrieved', ServiceCheckResource::collection($serviceChecks)));
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
}
