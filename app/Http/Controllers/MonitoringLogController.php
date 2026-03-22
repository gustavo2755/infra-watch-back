<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Resources\MonitoringLogCollectionResource;
use App\Resources\MonitoringLogResource;
use App\Resources\SuccessResource;

/**
 * Handles monitoring log endpoints.
 */
final class MonitoringLogController
{
    private const DEFAULT_PER_PAGE = 50;
    private const MAX_PER_PAGE = 200;

    public function __construct(
        private readonly MonitoringLogRepository $monitoringLogRepository,
        private readonly MonitoringLogServiceCheckRepository $monitoringLogServiceCheckRepository
    ) {
    }

    public function list(Request $request): void
    {
        $serverId = $request->getQuery('server_id');
        $from = $request->getQuery('from');
        $to = $request->getQuery('to');
        $alertsOnly = $request->getQuery('alerts_only');
        [$page, $perPage] = $this->resolvePagination($request);
        $serverIdFilter = $serverId !== null ? (int) $serverId : null;

        if ($alertsOnly !== null && in_array(strtolower($alertsOnly), ['1', 'true', 'on'], true)) {
            $logs = $this->monitoringLogRepository->listAlertsPaginated($serverIdFilter, $page, $perPage);
            $total = $this->monitoringLogRepository->countAlerts($serverIdFilter);
            $meta = $this->buildPaginationMeta($page, $perPage, $total);
            Response::json(
                SuccessResource::make('Monitoring alerts retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta))
            );
            return;
        }

        if ($from !== null && $to !== null) {
            $logs = $this->monitoringLogRepository->listByPeriodPaginated($from, $to, $serverIdFilter, $page, $perPage);
            $total = $this->monitoringLogRepository->countByPeriod($from, $to, $serverIdFilter);
            $meta = $this->buildPaginationMeta($page, $perPage, $total);
            Response::json(
                SuccessResource::make('Monitoring logs retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta))
            );
            return;
        }

        if ($serverId !== null) {
            $logs = $this->monitoringLogRepository->listByServerIdPaginated((int) $serverId, $page, $perPage);
            $total = $this->monitoringLogRepository->countByServerId((int) $serverId);
            $meta = $this->buildPaginationMeta($page, $perPage, $total);
            Response::json(
                SuccessResource::make('Monitoring logs retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta))
            );
            return;
        }

        $logs = $this->monitoringLogRepository->listRecentPaginated($page, $perPage);
        $total = $this->monitoringLogRepository->countAll();
        $meta = $this->buildPaginationMeta($page, $perPage, $total);
        Response::json(SuccessResource::make('Monitoring logs retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta)));
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $log = $this->monitoringLogRepository->findById($id);

        if ($log === null) {
            throw new HttpException('Monitoring log not found', 404);
        }

        $results = $this->monitoringLogServiceCheckRepository->listByMonitoringLogId($id);
        Response::json(SuccessResource::make('Monitoring log retrieved', MonitoringLogResource::make($log, $results)));
    }

    public function listByServer(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');
        [$page, $perPage] = $this->resolvePagination($request);
        $logs = $this->monitoringLogRepository->listByServerIdPaginated($serverId, $page, $perPage);
        $total = $this->monitoringLogRepository->countByServerId($serverId);
        $meta = $this->buildPaginationMeta($page, $perPage, $total);
        Response::json(SuccessResource::make('Monitoring logs retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta)));
    }

    public function dashboard(Request $request): void
    {
        $serverId = (int) $request->getParam('serverId');
        [$page, $perPage] = $this->resolvePagination($request);
        $logs = $this->monitoringLogRepository->listForDashboardPaginated($serverId, $page, $perPage);
        $total = $this->monitoringLogRepository->countByServerId($serverId);
        $meta = $this->buildPaginationMeta($page, $perPage, $total);
        Response::json(
            SuccessResource::make('Monitoring dashboard logs retrieved', MonitoringLogCollectionResource::makePaginated($logs, $meta))
        );
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePagination(Request $request): array
    {
        $page = max(1, (int) ($request->getQuery('page') ?? '1'));
        $perPageRaw = $request->getQuery('per_page');
        if ($perPageRaw === null || $perPageRaw === '') {
            $perPageRaw = $request->getQuery('limit') ?? (string) self::DEFAULT_PER_PAGE;
        }
        $perPage = max(1, (int) $perPageRaw);
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
