<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ServiceRequest\Dto\Factory\TicketReadDtoFactory;
use App\ServiceRequest\Dto\TicketReadDto;
use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Message\Query\GetTicketsQuery;
use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProviderInterface<TicketReadDto>
 */
final class TicketCollectionProvider implements ProviderInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $queryBus,
        private TicketReadDtoFactory $ticketReadDtoFactory,
    ) {
        $this->messageBus = $queryBus;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $filters = $context['filters'] ?? [];
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['itemsPerPage'] ?? 10);
        $sort = $filters['order'] ?? [];

        unset($filters['page'], $filters['itemsPerPage'], $filters['order']);

        /** @var Paginator<Ticket> $paginator */
        $paginator = $this->handle(new GetTicketsQuery($page, $limit, $filters, $sort));

        $totalItems = count($paginator);

        $dtos = [];
        foreach ($paginator as $ticket) {
            $dtos[] = $this->ticketReadDtoFactory->create($ticket);
        }

        return new TraversablePaginator(
            new ArrayIterator($dtos),
            (float) $page,
            (float) $limit,
            (float) $totalItems
        );
    }
}
