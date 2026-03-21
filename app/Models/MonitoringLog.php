<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Monitoring log model representing a monitoring execution for a server.
 */
final class MonitoringLog
{
    private ?int $id = null;
    private ?int $serverId = null;
    private ?string $checkedAt = null;
    private bool $isUp = false;
    private ?float $cpuUsagePercent = null;
    private ?float $ramUsagePercent = null;
    private ?float $diskUsagePercent = null;
    private ?float $bandwidthUsagePercent = null;
    private bool $isAlert = false;
    private ?string $alertType = null;
    private ?string $errorMessage = null;
    private ?string $sentToEmail = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        ?int $id = null,
        ?int $serverId = null,
        ?string $checkedAt = null,
        bool $isUp = false,
        ?float $cpuUsagePercent = null,
        ?float $ramUsagePercent = null,
        ?float $diskUsagePercent = null,
        ?float $bandwidthUsagePercent = null,
        bool $isAlert = false,
        ?string $alertType = null,
        ?string $errorMessage = null,
        ?string $sentToEmail = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->serverId = $serverId;
        $this->checkedAt = $checkedAt;
        $this->isUp = $isUp;
        $this->cpuUsagePercent = $cpuUsagePercent;
        $this->ramUsagePercent = $ramUsagePercent;
        $this->diskUsagePercent = $diskUsagePercent;
        $this->bandwidthUsagePercent = $bandwidthUsagePercent;
        $this->isAlert = $isAlert;
        $this->alertType = $alertType;
        $this->errorMessage = $errorMessage;
        $this->sentToEmail = $sentToEmail;
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

    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(?int $serverId): self
    {
        $this->serverId = $serverId;
        return $this;
    }

    public function getCheckedAt(): ?string
    {
        return $this->checkedAt;
    }

    public function setCheckedAt(?string $checkedAt): self
    {
        $this->checkedAt = $checkedAt;
        return $this;
    }

    public function getIsUp(): bool
    {
        return $this->isUp;
    }

    public function setIsUp(bool $isUp): self
    {
        $this->isUp = $isUp;
        return $this;
    }

    public function getCpuUsagePercent(): ?float
    {
        return $this->cpuUsagePercent;
    }

    public function setCpuUsagePercent(?float $cpuUsagePercent): self
    {
        $this->cpuUsagePercent = $cpuUsagePercent;
        return $this;
    }

    public function getRamUsagePercent(): ?float
    {
        return $this->ramUsagePercent;
    }

    public function setRamUsagePercent(?float $ramUsagePercent): self
    {
        $this->ramUsagePercent = $ramUsagePercent;
        return $this;
    }

    public function getDiskUsagePercent(): ?float
    {
        return $this->diskUsagePercent;
    }

    public function setDiskUsagePercent(?float $diskUsagePercent): self
    {
        $this->diskUsagePercent = $diskUsagePercent;
        return $this;
    }

    public function getBandwidthUsagePercent(): ?float
    {
        return $this->bandwidthUsagePercent;
    }

    public function setBandwidthUsagePercent(?float $bandwidthUsagePercent): self
    {
        $this->bandwidthUsagePercent = $bandwidthUsagePercent;
        return $this;
    }

    public function getIsAlert(): bool
    {
        return $this->isAlert;
    }

    public function setIsAlert(bool $isAlert): self
    {
        $this->isAlert = $isAlert;
        return $this;
    }

    public function getAlertType(): ?string
    {
        return $this->alertType;
    }

    public function setAlertType(?string $alertType): self
    {
        $this->alertType = $alertType;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getSentToEmail(): ?string
    {
        return $this->sentToEmail;
    }

    public function setSentToEmail(?string $sentToEmail): self
    {
        $this->sentToEmail = $sentToEmail;
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
     * @param array<string, mixed> $data Validated payload for monitoring log creation.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            null,
            isset($data['server_id']) ? (int) $data['server_id'] : null,
            $data['checked_at'] ?? null,
            isset($data['is_up']) ? (bool) $data['is_up'] : false,
            isset($data['cpu_usage_percent']) ? (float) $data['cpu_usage_percent'] : null,
            isset($data['ram_usage_percent']) ? (float) $data['ram_usage_percent'] : null,
            isset($data['disk_usage_percent']) ? (float) $data['disk_usage_percent'] : null,
            isset($data['bandwidth_usage_percent']) ? (float) $data['bandwidth_usage_percent'] : null,
            isset($data['is_alert']) ? (bool) $data['is_alert'] : false,
            $data['alert_type'] ?? null,
            $data['error_message'] ?? null,
            $data['sent_to_email'] ?? null,
            null,
            null
        );
    }
}
