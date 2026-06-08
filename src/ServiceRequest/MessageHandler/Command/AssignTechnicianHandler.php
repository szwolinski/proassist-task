<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Command;

use ApiPlatform\HttpCache\PurgerInterface;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Message\Command\AssignTechnicianCommand;
use App\ServiceRequest\Repository\TechnicianRepository;
use App\ServiceRequest\Repository\TicketRepository;
use App\ServiceRequest\Security\TicketVoter;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsMessageHandler]
final readonly class AssignTechnicianHandler
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TechnicianRepository $technicianRepository,
        private WorkflowInterface $ticketStatusStateMachine,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private PurgerInterface $purger,
    ) {
    }

    public function __invoke(AssignTechnicianCommand $command): void
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

        if (!$this->security->isGranted(TicketVoter::ASSIGN, $ticket)) {
            throw new AccessDeniedException('You do not have permission to assign technicians to this ticket.');
        }

        $technician = $this->technicianRepository->find($command->technicianId);

        if (!$technician || !$technician->isActive()) {
            throw new BadRequestHttpException('Technician not found or is inactive.');
        }

        try {
            if ($ticket->getStatus() !== TicketStatus::ASSIGNED) {
                $this->ticketStatusStateMachine->apply($ticket, 'assign');
            }
        } catch (Exception) {
            throw new BadRequestHttpException('Cannot assign technician in the current ticket status.');
        }

        $ticket->setAssignedTechnician($technician);
        $this->entityManager->flush();

        $this->purger->purge([
            sprintf('/api/tickets/%d', $ticket->getId()),
            '/api/tickets'
        ]);
    }
}
