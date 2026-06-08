<?php

declare(strict_types=1);

namespace App\ServiceRequest\EventSubscriber;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Entity\TicketHistory;
use App\ServiceRequest\Enum\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class TicketHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.ticket_status.completed' => 'onTicketTransitionCompleted',
        ];
    }

    /**
     * @param CompletedEvent<Ticket> $event
     */
    public function onTicketTransitionCompleted(CompletedEvent $event): void
    {
        /** @var Ticket $ticket */
        $ticket = $event->getSubject();

        $history = new TicketHistory();
        $history->setTicket($ticket);

        $transition = $event->getTransition();

        Assert::notNull($transition);

        $fromPlaces = $transition->getFroms();

        $oldStatusString = reset($fromPlaces);
        $history->setOldStatus(TicketStatus::from($oldStatusString));
        $history->setNewStatus($ticket->getStatus());

        $user = $this->security->getUser();
        $history->setChangedBy($user ? $user->getUserIdentifier() : 'SYSTEM');

        $this->entityManager->persist($history);
    }
}
