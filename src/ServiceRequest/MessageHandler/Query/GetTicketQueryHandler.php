<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Query;

use App\ServiceRequest\Dto\Factory\TicketReadDtoFactory;
use App\ServiceRequest\Dto\TicketReadDto;
use App\ServiceRequest\Message\Query\GetTicketQuery;
use App\ServiceRequest\Repository\TicketRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetTicketQueryHandler
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TicketReadDtoFactory $ticketReadDtoFactory,
    ) {
    }

    public function __invoke(GetTicketQuery $query): TicketReadDto
    {
        $ticket = $this->ticketRepository->find($query->id);

        if (!$ticket) {
            throw new NotFoundHttpException(sprintf('Ticker with id %s not found.', $query->id));
        }

        return $this->ticketReadDtoFactory->create($ticket);
    }
}
