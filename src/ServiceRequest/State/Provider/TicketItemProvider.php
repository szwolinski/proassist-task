<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ServiceRequest\Dto\TicketReadDto;
use App\ServiceRequest\Message\Query\GetTicketQuery;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProviderInterface<TicketReadDto>
 */
final class TicketItemProvider implements ProviderInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $ticketId = (int) $uriVariables['id'];
        return $this->handle(new GetTicketQuery($ticketId));
    }
}
