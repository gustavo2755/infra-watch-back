<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Server model representing a monitored server entity.
 */
final class Server
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $ipAddress = null;
    private bool $isActive = true;
    private bool $monitorResources = true;
    private ?float $cpuTotal = null;
    private ?float $ramTotal = null;
    private ?float $diskTotal = null;
    private ?int $checkIntervalSeconds = null;
    private ?string $lastCheckAt = null;
    private ?int $retentionDays = null;
    private ?float $cpuAlertThreshold = null;
    private ?float $ramAlertThreshold = null;
    private ?float $diskAlertThreshold = null;
    private ?float $bandwidthAlertThreshold = null;
    private bool $alertCpuEnabled = true;
    private bool $alertRamEnabled = true;
    private bool $alertDiskEnabled = true;
    private bool $alertBandwidthEnabled = true;
    private ?int $createdBy = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $description = null,
        ?string $ipAddress = null,
        bool $isActive = true,
        bool $monitorResources = true,
        ?float $cpuTotal = null,
        ?float $ramTotal = null,
        ?float $diskTotal = null,
        ?int $checkIntervalSeconds = null,
        ?string $lastCheckAt = null,
        ?int $retentionDays = null,
        ?float $cpuAlertThreshold = null,
        ?float $ramAlertThreshold = null,
        ?float $diskAlertThreshold = null,
        ?float $bandwidthAlertThreshold = null,
        bool $alertCpuEnabled = true,
        bool $alertRamEnabled = true,
        bool $alertDiskEnabled = true,
        bool $alertBandwidthEnabled = true,
        ?int $createdBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->ipAddress = $ipAddress;
        $this->isActive = $isActive;
        $this->monitorResources = $monitorResources;
        $this->cpuTotal = $cpuTotal;
        $this->ramTotal = $ramTotal;
        $this->diskTotal = $diskTotal;
        $this->checkIntervalSeconds = $checkIntervalSeconds;
        $this->lastCheckAt = $lastCheckAt;
        $this->retentionDays = $retentionDays;
        $this->cpuAlertThreshold = $cpuAlertThreshold;
        $this->ramAlertThreshold = $ramAlertThreshold;
        $this->diskAlertThreshold = $diskAlertThreshold;
        $this->bandwidthAlertThreshold = $bandwidthAlertThreshold;
        $this->alertCpuEnabled = $alertCpuEnabled;
        $this->alertRamEnabled = $alertRamEnabled;
        $this->alertDiskEnabled = $alertDiskEnabled;
        $this->alertBandwidthEnabled = $alertBandwidthEnabled;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getMonitorResources(): bool
    {
        return $this->monitorResources;
    }

    public function setMonitorResources(bool $monitorResources): self
    {
        $this->monitorResources = $monitorResources;
        return $this;
    }

    public function getCpuTotal(): ?float
    {
        return $this->cpuTotal;
    }

    public function setCpuTotal(?float $cpuTotal): self
    {
        $this->cpuTotal = $cpuTotal;
        return $this;
    }

    public function getRamTotal(): ?float
    {
        return $this->ramTotal;
    }

    public function setRamTotal(?float $ramTotal): self
    {
        $this->ramTotal = $ramTotal;
        return $this;
    }

    public function getDiskTotal(): ?float
    {
        return $this->diskTotal;
    }

    public function setDiskTotal(?float $diskTotal): self
    {
        $this->diskTotal = $diskTotal;
        return $this;
    }

    public function getCheckIntervalSeconds(): ?int
    {
        return $this->checkIntervalSeconds;
    }

    public function setCheckIntervalSeconds(?int $checkIntervalSeconds): self
    {
        $this->checkIntervalSeconds = $checkIntervalSeconds;
        return $this;
    }

    public function getLastCheckAt(): ?string
    {
        return $this->lastCheckAt;
    }

    public function setLastCheckAt(?string $lastCheckAt): self
    {
        $this->lastCheckAt = $lastCheckAt;
        return $this;
    }

    public function getRetentionDays(): ?int
    {
        return $this->retentionDays;
    }

    public function setRetentionDays(?int $retentionDays): self
    {
        $this->retentionDays = $retentionDays;
        return $this;
    }

    public function getCpuAlertThreshold(): ?float
    {
        return $this->cpuAlertThreshold;
    }

    public function setCpuAlertThreshold(?float $cpuAlertThreshold): self
    {
        $this->cpuAlertThreshold = $cpuAlertThreshold;
        return $this;
    }

    public function getRamAlertThreshold(): ?float
    {
        return $this->ramAlertThreshold;
    }

    public function setRamAlertThreshold(?float $ramAlertThreshold): self
    {
        $this->ramAlertThreshold = $ramAlertThreshold;
        return $this;
    }

    public function getDiskAlertThreshold(): ?float
    {
        return $this->diskAlertThreshold;
    }

    public function setDiskAlertThreshold(?float $diskAlertThreshold): self
    {
        $this->diskAlertThreshold = $diskAlertThreshold;
        return $this;
    }

    public function getBandwidthAlertThreshold(): ?float
    {
        return $this->bandwidthAlertThreshold;
    }

    public function setBandwidthAlertThreshold(?float $bandwidthAlertThreshold): self
    {
        $this->bandwidthAlertThreshold = $bandwidthAlertThreshold;
        return $this;
    }

    public function getAlertCpuEnabled(): bool
    {
        return $this->alertCpuEnabled;
    }

    public function setAlertCpuEnabled(bool $alertCpuEnabled): self
    {
        $this->alertCpuEnabled = $alertCpuEnabled;
        return $this;
    }

    public function getAlertRamEnabled(): bool
    {
        return $this->alertRamEnabled;
    }

    public function setAlertRamEnabled(bool $alertRamEnabled): self
    {
        $this->alertRamEnabled = $alertRamEnabled;
        return $this;
    }

    public function getAlertDiskEnabled(): bool
    {
        return $this->alertDiskEnabled;
    }

    public function setAlertDiskEnabled(bool $alertDiskEnabled): self
    {
        $this->alertDiskEnabled = $alertDiskEnabled;
        return $this;
    }

    public function getAlertBandwidthEnabled(): bool
    {
        return $this->alertBandwidthEnabled;
    }

    public function setAlertBandwidthEnabled(bool $alertBandwidthEnabled): self
    {
        $this->alertBandwidthEnabled = $alertBandwidthEnabled;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @param array<string, mixed> $data Validated payload (e.g. from StoreServerRequest)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            null,
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['ip_address'] ?? null,
            isset($data['is_active']) ? (bool) $data['is_active'] : true,
            isset($data['monitor_resources']) ? (bool) $data['monitor_resources'] : true,
            isset($data['cpu_total']) ? (float) $data['cpu_total'] : null,
            isset($data['ram_total']) ? (float) $data['ram_total'] : null,
            isset($data['disk_total']) ? (float) $data['disk_total'] : null,
            isset($data['check_interval_seconds']) ? (int) $data['check_interval_seconds'] : null,
            $data['last_check_at'] ?? null,
            isset($data['retention_days']) ? (int) $data['retention_days'] : null,
            isset($data['cpu_alert_threshold']) ? (float) $data['cpu_alert_threshold'] : null,
            isset($data['ram_alert_threshold']) ? (float) $data['ram_alert_threshold'] : null,
            isset($data['disk_alert_threshold']) ? (float) $data['disk_alert_threshold'] : null,
            isset($data['bandwidth_alert_threshold']) ? (float) $data['bandwidth_alert_threshold'] : null,
            isset($data['alert_cpu_enabled']) ? (bool) $data['alert_cpu_enabled'] : true,
            isset($data['alert_ram_enabled']) ? (bool) $data['alert_ram_enabled'] : true,
            isset($data['alert_disk_enabled']) ? (bool) $data['alert_disk_enabled'] : true,
            isset($data['alert_bandwidth_enabled']) ? (bool) $data['alert_bandwidth_enabled'] : true,
            isset($data['created_by']) ? (int) $data['created_by'] : null,
            null,
            null
        );
    }
}
