<?php

declare(strict_types=1);

namespace App\ServiceRequest\Entity;

use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Repository\TicketRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Index(name: 'idx_ticket_status', columns: ['status'])]
#[ORM\Index(name: 'idx_ticket_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_ticket_tech_status', columns: ['assigned_technician_id', 'status'])]
#[ORM\HasLifecycleCallbacks]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(enumType: TicketPriority::class)]
    private ?TicketPriority $priority = null;

    #[ORM\Column(enumType: TicketStatus::class)]
    private ?TicketStatus $status = TicketStatus::NEW;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $closedAt = null;

    #[ORM\ManyToOne(targetEntity: Technician::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Technician $assignedTechnician = null;

    #[ORM\ManyToOne(targetEntity: Device::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Device $device = null;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;

    #[ORM\PrePersist]
    public function setTimestampsOnCreate(): void
    {
        $this->createdAt ??= new DateTimeImmutable();
        $this->updatedAt ??= new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        $this->closedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPriority(): ?TicketPriority
    {
        return $this->priority;
    }

    public function setPriority(?TicketPriority $priority): void
    {
        $this->priority = $priority;
    }

    public function getStatus(): ?TicketStatus
    {
        return $this->status;
    }

    public function setStatus(?TicketStatus $status): void
    {
        $this->status = $status;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function getAssignedTechnician(): ?Technician
    {
        return $this->assignedTechnician;
    }

    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    public function setAssignedTechnician(?Technician $assignedTechnician): void
    {
        $this->assignedTechnician = $assignedTechnician;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
