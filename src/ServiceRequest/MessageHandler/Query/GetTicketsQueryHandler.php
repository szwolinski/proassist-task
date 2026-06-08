<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Query;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Message\Query\GetTicketsQuery;
use App\ServiceRequest\Repository\TicketRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetTicketsQueryHandler
{
    public function __construct(
        private TicketRepository $ticketRepository,
    ) {
    }

    /**
     * @return Paginator<Ticket>
     */
    public function __invoke(GetTicketsQuery $query): Paginator
    {
        return $this->ticketRepository->getFilteredTickets(
            $query->page,
            $query->limit,
            $query->filters,
            $query->sort
        );
    }
}
