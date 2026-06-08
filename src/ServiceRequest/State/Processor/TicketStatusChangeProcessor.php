<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ServiceRequest\Dto\ChangeTicketStatusPayload;
use App\ServiceRequest\Message\Command\ChangeTicketStatusCommand;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<ChangeTicketStatusPayload, void>
 */
final readonly class TicketStatusChangeProcessor implements ProcessorInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    /**
     * @param ChangeTicketStatusPayload $data
     * @throws ExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $ticketId = (int) $uriVariables['id'];

        $command = new ChangeTicketStatusCommand(
            ticketId: $ticketId,
            transition: $data->transition,
            expectedVersion: $data->expectedVersion
        );

        $this->commandBus->dispatch($command);
    }
}
