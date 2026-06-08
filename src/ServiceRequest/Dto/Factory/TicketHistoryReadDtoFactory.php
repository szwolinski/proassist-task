<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto\Factory;

use App\ServiceRequest\Dto\TicketHistoryReadDto;
use App\ServiceRequest\Entity\TicketHistory;
use DateTimeInterface;
use LogicException;
use Webmozart\Assert\Assert;

final readonly class TicketHistoryReadDtoFactory
{
    public function create(TicketHistory $entry): TicketHistoryReadDto
    {
        Assert::notNull($entry->getId());
        Assert::notNull($entry->getTicket()?->getId());
        Assert::notNull($entry->getChangedAt());
        Assert::notNull($entry->getOldStatus());
        Assert::notNull($entry->getNewStatus());

        $dto = new TicketHistoryReadDto();

        $dto->id = $entry->getId();
        $dto->ticketId = $entry->getTicket()->getId();

        $dto->oldStatus = $entry->getOldStatus()->value;
        $dto->newStatus = $entry->getNewStatus()->value;

        $dto->changedAt = $entry->getChangedAt()->format(DateTimeInterface::ATOM);
        $dto->changedBy = $entry->getChangedBy() ?? '';

        return $dto;
    }
}
