<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User model representing a user entity.
 */
final class User
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
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
     * @param array{name?: string|null, email: string, password: string} $data Validated payload
     */
    public static function fromArray(array $data): self
    {
        return new self(
            null,
            $data['name'] ?? null,
            $data['email'],
            $data['password'],
            null,
            null
        );
    }
}
