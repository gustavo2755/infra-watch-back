<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Models\MonitoringLog;
use App\Models\MonitoringLogServiceCheck;
use App\Models\Server;
use App\Repositories\MonitoringLogRepository;
use App\Repositories\MonitoringLogServiceCheckRepository;
use App\Repositories\MonitoringQueueRepository;
use App\Repositories\ServerServiceCheckRepository;
use PDOException;

/**
 * Service responsible for mocked monitoring execution and persistence.
 */
final class MonitoringService
{
    public function __construct(
        private MonitoringLogRepository $monitoringLogRepository,
        private MonitoringLogServiceCheckRepository $monitoringLogServiceCheckRepository,
        private ServerServiceCheckRepository $serverServiceCheckRepository,
        private MonitoringQueueRepository $monitoringQueueRepository
    ) {
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
        $metrics = $this->generateMockMetrics();
        $isUp = true;
        $alertTypes = $this->resolveAlertTypes($server, $metrics);
        $isAlert = $alertTypes !== [];
        $alertType = $isAlert ? implode(',', $alertTypes) : null;
        $errorMessage = $isUp ? null : 'Mocked availability failure';
        $sentToEmail = $isAlert ? 'alerts@infra.watch' : null;

        try {
            $log = new MonitoringLog(
                null,
                $serverId,
                $checkedAt,
                $isUp,
                $metrics['cpu_usage_percent'],
                $metrics['ram_usage_percent'],
                $metrics['disk_usage_percent'],
                $metrics['bandwidth_usage_percent'],
                $isAlert,
                $alertType,
                $errorMessage,
                $sentToEmail
            );

            $logId = $this->monitoringLogRepository->create($log);
            $log->setId($logId);

            $serviceChecks = $this->serverServiceCheckRepository->listByServerId($serverId);
            foreach ($serviceChecks as $serviceCheck) {
                $isRunning = random_int(0, 1) === 1;
                $outputMessage = $isRunning ? 'Service is running' : 'Service is not running';
                $result = new MonitoringLogServiceCheck(
                    null,
                    $logId,
                    $serviceCheck->getId(),
                    $isRunning,
                    $outputMessage
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
     * @return array{cpu_usage_percent: float, ram_usage_percent: float, disk_usage_percent: float, bandwidth_usage_percent: float}
     */
    private function generateMockMetrics(): array
    {
        return [
            'cpu_usage_percent' => $this->randomMetric(),
            'ram_usage_percent' => $this->randomMetric(),
            'disk_usage_percent' => $this->randomMetric(),
            'bandwidth_usage_percent' => $this->randomMetric(),
        ];
    }

    private function randomMetric(): float
    {
        return random_int(0, 5000) / 100;
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
