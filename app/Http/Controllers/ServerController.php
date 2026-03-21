<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Requests\StoreServerRequest;
use App\Requests\UpdateServerRequest;
use App\Contracts\ServerServiceInterface;
use App\Resources\ServerCollectionResource;
use App\Resources\ServerResource;
use App\Resources\SuccessResource;

/**
 * Handles server endpoints.
 */
final class ServerController
{
    public function __construct(
        private readonly ServerServiceInterface $serverService,
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

        Response::json(SuccessResource::make('Server updated', ServerResource::make($server)));
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');

        $server = $this->serverService->findById($id);

        Response::json(SuccessResource::make('Server retrieved', ServerResource::make($server)));
    }

    public function list(Request $request): void
    {
        $name = $request->getQuery('name');
        $isActive = $request->getQuery('is_active');

        if ($name !== null && $name !== '') {
            $servers = $this->serverService->filterByName($name);
        } elseif ($isActive !== null && $isActive !== '') {
            $servers = $this->serverService->filterByIsActive(in_array($isActive, ['1', 'true', 'on'], true));
        } else {
            $servers = $this->serverService->list();
        }

        Response::json(SuccessResource::make('Servers retrieved', ServerCollectionResource::make($servers)));
    }

}
