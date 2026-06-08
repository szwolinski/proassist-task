<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto\Factory;

use App\ServiceRequest\Dto\TicketReadDto;
use App\ServiceRequest\Entity\Ticket;
use DateTimeInterface;
use Symfony\Component\Workflow\Registry;
use Webmozart\Assert\Assert;

final readonly class TicketReadDtoFactory
{
    public function __construct(
        private Registry $workflowRegistry
    ) {
    }

    public function create(Ticket $ticket): TicketReadDto
    {
        Assert::notNull($ticket->getId());
        Assert::notNull($ticket->getCreatedAt());
        Assert::notNull($ticket->getPriority());
        Assert::notNull($ticket->getStatus());

        $dto = new TicketReadDto();

        $dto->id = $ticket->getId();
        $dto->title = $ticket->getTitle() ?? '';
        $dto->status = $ticket->getStatus()->value;
        $dto->priority = $ticket->getPriority()->value;
        $dto->description = $ticket->getDescription() ?? '';
        $dto->createdAt = $ticket->getCreatedAt()->format(DateTimeInterface::ATOM);
        $dto->deviceSerialNumber = $ticket->getDevice()?->getSerialNumber();
        $dto->version = $ticket->getVersion();

        if ($technician = $ticket->getAssignedTechnician()) {
            $dto->assignedTechnicianName = sprintf('%s %s', $technician->getFirstName(), $technician->getLastName());
        }

        $workflow = $this->workflowRegistry->get($ticket);

        $dto->availableTransitions = array_map(
            static fn ($transition) => $transition->getName(),
            $workflow->getEnabledTransitions($ticket)
        );

        return $dto;
    }
}
