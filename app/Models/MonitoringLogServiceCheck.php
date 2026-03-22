<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Monitoring log service check model for service-level monitoring results.
 */
final class MonitoringLogServiceCheck
{
    private ?int $id = null;
    private ?int $monitoringLogId = null;
    private ?int $serviceCheckId = null;
    private bool $isRunning = false;
    private ?string $outputMessage = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        ?int $id = null,
        ?int $monitoringLogId = null,
        ?int $serviceCheckId = null,
        bool $isRunning = false,
        ?string $outputMessage = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->monitoringLogId = $monitoringLogId;
        $this->serviceCheckId = $serviceCheckId;
        $this->isRunning = $isRunning;
        $this->outputMessage = $outputMessage;
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

    public function getMonitoringLogId(): ?int
    {
        return $this->monitoringLogId;
    }

    public function setMonitoringLogId(?int $monitoringLogId): self
    {
        $this->monitoringLogId = $monitoringLogId;
        return $this;
    }

    public function getServiceCheckId(): ?int
    {
        return $this->serviceCheckId;
    }

    public function setServiceCheckId(?int $serviceCheckId): self
    {
        $this->serviceCheckId = $serviceCheckId;
        return $this;
    }

    public function getIsRunning(): bool
    {
        return $this->isRunning;
    }

    public function setIsRunning(bool $isRunning): self
    {
        $this->isRunning = $isRunning;
        return $this;
    }

    public function getOutputMessage(): ?string
    {
        return $this->outputMessage;
    }

    public function setOutputMessage(?string $outputMessage): self
    {
        $this->outputMessage = $outputMessage;
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
     * @param array<string, mixed> $data Validated payload for monitoring log service check creation.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            null,
            isset($data['monitoring_log_id']) ? (int) $data['monitoring_log_id'] : null,
            isset($data['service_check_id']) ? (int) $data['service_check_id'] : null,
            isset($data['is_running']) ? (bool) $data['is_running'] : false,
            $data['output_message'] ?? null,
            null,
            null
        );
    }
}
