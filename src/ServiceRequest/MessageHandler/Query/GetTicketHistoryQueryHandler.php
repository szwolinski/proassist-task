<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Query;

use App\ServiceRequest\Entity\TicketHistory;
use App\ServiceRequest\Message\Query\GetTicketHistoryQuery;
use App\ServiceRequest\Repository\TicketHistoryRepository;
use App\ServiceRequest\Repository\TicketRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetTicketHistoryQueryHandler
{
    public function __construct(
        private TicketHistoryRepository $historyRepository,
        private TicketRepository $ticketRepository,
    ) {
    }

    /**
     * @return TicketHistory[]
     */
    public function __invoke(GetTicketHistoryQuery $query): array
    {
        $ticket = $this->ticketRepository->find($query->id);

        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found');
        }

        return $this->historyRepository->findBy(
            ['ticket' => $query->id],
            ['changedAt' => 'DESC']
        );
    }
}
