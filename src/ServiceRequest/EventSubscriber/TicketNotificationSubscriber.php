<?php

declare(strict_types=1);

namespace App\ServiceRequest\EventSubscriber;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Message\TicketDoneMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class TicketNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus
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
     * @throws ExceptionInterface
     */
    public function onTicketTransitionCompleted(CompletedEvent $event): void
    {
        /** @var Ticket $ticket */
        $ticket = $event->getSubject();

        if ($ticket->getStatus() === TicketStatus::DONE) {
            Assert::integer($ticket->getId());
            $this->messageBus->dispatch(new TicketDoneMessage($ticket->getId()));
        }
    }
}
