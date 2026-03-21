<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Models\MonitoringLog;
use App\Models\MonitoringLogServiceCheck;
use App\Models\Server;
use App\Monitoring\MockMetricGenerator;
use App\Monitoring\MockServiceCheckGenerator;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Repositories\MonitoringQueueRepository;
use App\Repositories\ServerServiceCheckRepository;
use PDOException;

/**
 * Orchestrates simulated monitoring of a server.
 * Uses mock generators for metrics and service checks, persists logs, and updates last_check_at.
 */
final class MonitoringService
{
    private MockMetricGenerator $metricGenerator;
    private MockServiceCheckGenerator $serviceCheckGenerator;

    public function __construct(
        private MonitoringLogRepository $monitoringLogRepository,
        private MonitoringLogServiceCheckRepository $monitoringLogServiceCheckRepository,
        private ServerServiceCheckRepository $serverServiceCheckRepository,
        private MonitoringQueueRepository $monitoringQueueRepository,
        ?MockMetricGenerator $metricGenerator = null,
        ?MockServiceCheckGenerator $serviceCheckGenerator = null
    ) {
        $this->metricGenerator = $metricGenerator ?? new MockMetricGenerator();
        $this->serviceCheckGenerator = $serviceCheckGenerator ?? new MockServiceCheckGenerator();
    }

    /**
     * Executes one mocked monitoring cycle for a server.
     *
     * @throws HttpException
     */
    public function processServer(Server $server): MonitoringLog
    {
        $serverId = $server->getId();

        if ($serverId === null) {
            throw new HttpException('Server id is required for monitoring', 400);
        }

        $checkedAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $isUp = true;

        $cpuUsage = null;
        $ramUsage = null;
        $diskUsage = null;
        $bandwidthUsage = null;
        $isAlert = false;
        $alertType = null;
        $sentToEmail = null;

        if ($server->getMonitorResources()) {
            $metrics = $this->metricGenerator->generate();
            $cpuUsage = $metrics['cpu_usage_percent'];
            $ramUsage = $metrics['ram_usage_percent'];
            $diskUsage = $metrics['disk_usage_percent'];
            $bandwidthUsage = $metrics['bandwidth_usage_percent'];
            $alertTypes = $this->resolveAlertTypes($server, $metrics);
            $isAlert = $alertTypes !== [];
            $alertType = $isAlert ? implode(',', $alertTypes) : null;
            $sentToEmail = $isAlert ? 'alerts@infra.watch' : null;
        }

        $errorMessage = $isUp ? null : 'Mocked availability failure';

        try {
            $log = new MonitoringLog(
                null,
                $serverId,
                $checkedAt,
                $isUp,
                $cpuUsage,
                $ramUsage,
                $diskUsage,
                $bandwidthUsage,
                $isAlert,
                $alertType,
                $errorMessage,
                $sentToEmail
            );

            $logId = $this->monitoringLogRepository->create($log);
            $log->setId($logId);

            $serviceChecks = $this->serverServiceCheckRepository->listByServerId($serverId);
            foreach ($serviceChecks as $serviceCheck) {
                $mock = $this->serviceCheckGenerator->generate();
                $result = new MonitoringLogServiceCheck(
                    null,
                    $logId,
                    $serviceCheck->getId(),
                    $mock['is_running'],
                    $mock['output_message']
                );
                $this->monitoringLogServiceCheckRepository->create($result);
            }

            $this->monitoringQueueRepository->touchLastCheckAt($serverId);

            return $log;
        } catch (PDOException $e) {
            throw new HttpException('Failed to persist monitoring data', 500, $e);
        }
    }

    /**
     * @param array{cpu_usage_percent: float, ram_usage_percent: float, disk_usage_percent: float, bandwidth_usage_percent: float} $metrics
     * @return list<string>
     */
    private function resolveAlertTypes(Server $server, array $metrics): array
    {
        $alertTypes = [];

        if ($server->getAlertCpuEnabled() && $server->getCpuAlertThreshold() !== null && $metrics['cpu_usage_percent'] > $server->getCpuAlertThreshold()) {
            $alertTypes[] = 'cpu';
        }

        if ($server->getAlertRamEnabled() && $server->getRamAlertThreshold() !== null && $metrics['ram_usage_percent'] > $server->getRamAlertThreshold()) {
            $alertTypes[] = 'ram';
        }

        if ($server->getAlertDiskEnabled() && $server->getDiskAlertThreshold() !== null && $metrics['disk_usage_percent'] > $server->getDiskAlertThreshold()) {
            $alertTypes[] = 'disk';
        }

        if ($server->getAlertBandwidthEnabled() && $server->getBandwidthAlertThreshold() !== null && $metrics['bandwidth_usage_percent'] > $server->getBandwidthAlertThreshold()) {
            $alertTypes[] = 'bandwidth';
        }

        return $alertTypes;
    }
}
