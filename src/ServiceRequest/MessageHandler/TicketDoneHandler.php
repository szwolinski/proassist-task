<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler;

use App\ServiceRequest\Message\TicketDoneMessage;
use App\ServiceRequest\Repository\TicketRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class TicketDoneHandler
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(TicketDoneMessage $message): void
    {
        $ticket = $this->ticketRepository->find($message->ticketId);

        if (!$ticket) {
            $this->logger->warning(sprintf('Ticket #%d not found for done notification.', $message->ticketId));
            return;
        }

        $this->logger->info(sprintf('Simulating email dispatch for done Ticket #%d...', $ticket->getId()));
    }
}
