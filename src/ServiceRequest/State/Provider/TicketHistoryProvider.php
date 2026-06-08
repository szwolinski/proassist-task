<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ServiceRequest\Dto\Factory\TicketHistoryReadDtoFactory;
use App\ServiceRequest\Dto\TicketHistoryReadDto;
use App\ServiceRequest\Entity\TicketHistory;
use App\ServiceRequest\Message\Query\GetTicketHistoryQuery;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProviderInterface<TicketHistoryReadDto>
 */
final class TicketHistoryProvider implements ProviderInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $queryBus,
        private readonly TicketHistoryReadDtoFactory $dtoFactory
    ) {
        $this->messageBus = $queryBus;
    }

    /**
     * @return TicketHistoryReadDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $ticketId = (int) $uriVariables['id'];

        /** @var TicketHistory[] $historyEntries */
        $historyEntries = $this->handle(new GetTicketHistoryQuery($ticketId));

        $dtos = [];

        foreach ($historyEntries as $entry) {
            $dtos[] = $this->dtoFactory->create($entry);
        }

        return $dtos;
    }
}
