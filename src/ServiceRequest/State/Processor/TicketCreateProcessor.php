<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ServiceRequest\Dto\Factory\TicketReadDtoFactory;
use App\ServiceRequest\Dto\TicketCreatePayload;
use App\ServiceRequest\Dto\TicketReadDto;
use App\ServiceRequest\Message\Command\CreateTicketCommand;
use App\ServiceRequest\Repository\TicketRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<TicketCreatePayload, TicketReadDto>
 */
final class TicketCreateProcessor implements ProcessorInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $commandBus,
        readonly private TicketRepository $ticketRepository,
        readonly private TicketReadDtoFactory $ticketReadDtoFactory,
    ) {
        $this->messageBus = $commandBus;
    }

    /**
     * @param TicketCreatePayload $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TicketReadDto
    {
        $ticket = $this->ticketRepository->find(
            $this->handle(
                new CreateTicketCommand($data->title, $data->description, $data->priority, $data->deviceId)
            )
        );

        if (null === $ticket) {
            throw new NotFoundHttpException('Ticket not found.');
        }

        return $this->ticketReadDtoFactory->create(
            $ticket,
        );
    }
}
