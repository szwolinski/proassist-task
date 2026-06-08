<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ServiceRequest\Dto\TicketAssignPayload;
use App\ServiceRequest\Message\Command\AssignTechnicianCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<TicketAssignPayload, JsonResponse>
 */
final class TicketAssignProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        /** @var TicketAssignPayload $data */
        $ticketId = $uriVariables['id'] ?? null;

        if ($ticketId === null) {
            throw new BadRequestHttpException('Ticket ID cannot be null.');
        }

        $command = new AssignTechnicianCommand(
            (int) $ticketId,
            $data->technicianId,
            $data->expectedVersion
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['message' => 'Technician assigned successfully.'], 200);
    }
}
