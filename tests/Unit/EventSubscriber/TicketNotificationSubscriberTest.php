<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\EventSubscriber\TicketNotificationSubscriber;
use App\ServiceRequest\Message\TicketDoneMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

final class TicketNotificationSubscriberTest extends TestCase
{
    private MessageBusInterface&MockObject $messageBusMock;
    private TicketNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->subscriber = new TicketNotificationSubscriber($this->messageBusMock);
    }

    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = TicketNotificationSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey('workflow.ticket_status.completed', $events);
        $this->assertSame('onTicketTransitionCompleted', $events['workflow.ticket_status.completed']);
    }

    public function testDispatchesMessageWhenTicketStatusIsDone(): void
    {
        $ticketId = 123;

        $ticketMock = $this->createMock(Ticket::class);
        $ticketMock->method('getStatus')->willReturn(TicketStatus::DONE);
        $ticketMock->method('getId')->willReturn($ticketId);

        $marking = new Marking();
        $transition = new Transition('dummy_transition', ['from_place'], ['to_place']);
        $event = new CompletedEvent($ticketMock, $marking, $transition);

        $this->messageBusMock->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (mixed $message) use ($ticketId) {
                return $message instanceof TicketDoneMessage && $message->ticketId === $ticketId;
            }))
            ->willReturn(new Envelope(new stdClass()));

        $this->subscriber->onTicketTransitionCompleted($event);
    }

    public function testDoesNotDispatchMessageWhenTicketStatusIsNotDone(): void
    {
        $ticketMock = $this->createMock(Ticket::class);
        $ticketMock->method('getStatus')->willReturn(TicketStatus::NEW);

        $marking = new Marking();
        $transition = new Transition('dummy_transition', ['from_place'], ['to_place']);
        $event = new CompletedEvent($ticketMock, $marking, $transition);

        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->subscriber->onTicketTransitionCompleted($event);
    }
}
