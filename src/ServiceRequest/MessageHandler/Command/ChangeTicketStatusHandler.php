<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Command;

use ApiPlatform\HttpCache\PurgerInterface;
use ApiPlatform\Metadata\Exception\AccessDeniedException;
use App\ServiceRequest\Message\Command\ChangeTicketStatusCommand;
use App\ServiceRequest\Repository\TicketRepository;
use App\ServiceRequest\Security\TicketVoter;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsMessageHandler]
final readonly class ChangeTicketStatusHandler
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private EntityManagerInterface $entityManager,
        private WorkflowInterface $ticketStatusStateMachine,
        private Security $security,
        private PurgerInterface $purger,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(ChangeTicketStatusCommand $command): void
    {
        $ticket = $this->ticketRepository->find($command->ticketId);

        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found.');
        }

        try {
            $this->entityManager->lock($ticket, LockMode::OPTIMISTIC, $command->expectedVersion);
        } catch (OptimisticLockException) {
            throw new ConflictHttpException('Ticket was modified by someone else.');
        }

        if (!$this->security->isGranted(TicketVoter::EDIT, $ticket)) {
            throw new AccessDeniedException('You do not have permission to edit this ticket.');
        }

        if (!$this->ticketStatusStateMachine->can($ticket, $command->transition)) {
            throw new BadRequestHttpException(sprintf('Cannot transition ticket to "%s".', $command->transition));
        }

        $this->ticketStatusStateMachine->apply($ticket, $command->transition);

        if ($command->transition === 'finish' || $command->transition === 'cancel') {
            $ticket->close();
        }

        $this->entityManager->flush();

        $this->purger->purge([
            sprintf('/api/tickets/%d', $ticket->getId()),
            '/api/tickets'
        ]);
    }
}
