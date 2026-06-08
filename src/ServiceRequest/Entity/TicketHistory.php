<?php

declare(strict_types=1);

namespace App\ServiceRequest\Entity;

use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Repository\TicketHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketHistoryRepository::class)]
#[ORM\Index(name: 'idx_ticket_hist_chrono', columns: ['ticket_id', 'changed_at'])]
#[ORM\HasLifecycleCallbacks]
class TicketHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Ticket $ticket = null;

    #[ORM\Column(enumType: TicketStatus::class, nullable: true)]
    private ?TicketStatus $oldStatus = null;

    #[ORM\Column(enumType: TicketStatus::class)]
    private ?TicketStatus $newStatus = null;

    #[ORM\Column]
    private ?DateTimeImmutable $changedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $changedBy = null;

    #[ORM\PrePersist]
    public function setChangedAtValue(): void
    {
        $this->changedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function getOldStatus(): ?TicketStatus
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?TicketStatus $oldStatus): void
    {
        $this->oldStatus = $oldStatus;
    }

    public function getNewStatus(): ?TicketStatus
    {
        return $this->newStatus;
    }

    public function setNewStatus(?TicketStatus $newStatus): void
    {
        $this->newStatus = $newStatus;
    }

    public function getChangedBy(): ?string
    {
        return $this->changedBy;
    }

    public function setChangedBy(?string $changedBy): void
    {
        $this->changedBy = $changedBy;
    }

    public function getChangedAt(): ?DateTimeImmutable
    {
        return $this->changedAt;
    }
}
