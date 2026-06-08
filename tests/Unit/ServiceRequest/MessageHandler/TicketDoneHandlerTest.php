<?php

declare(strict_types=1);

namespace App\Tests\Unit\ServiceRequest\MessageHandler;

use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Message\TicketDoneMessage;
use App\ServiceRequest\MessageHandler\TicketDoneHandler;
use App\ServiceRequest\Repository\TicketRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class TicketDoneHandlerTest extends TestCase
{
    private TicketRepository&MockObject $ticketRepositoryMock;
    private LoggerInterface&MockObject $loggerMock;
    private TicketDoneHandler $handler;

    protected function setUp(): void
    {
        $this->ticketRepositoryMock = $this->createMock(TicketRepository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->handler = new TicketDoneHandler(
            $this->ticketRepositoryMock,
            $this->loggerMock
        );
    }

    public function testInvokeLogsInfoWhenTicketExists(): void
    {
        $ticketId = 42;
        $message = new TicketDoneMessage($ticketId);;

        $ticketMock = $this->createMock(Ticket::class);
        $ticketMock->method('getId')->willReturn($ticketId);

        $this->ticketRepositoryMock->expects($this->once())
            ->method('find')
            ->with($ticketId)
            ->willReturn($ticketMock);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(sprintf('Simulating email dispatch for done Ticket #%d...', $ticketId));

        $this->loggerMock->expects($this->never())->method('warning');

        ($this->handler)($message);
    }

    public function testInvokeLogsWarningWhenTicketDoesNotExist(): void
    {
        $ticketId = 999;
        $message = new TicketDoneMessage($ticketId);

        $this->ticketRepositoryMock->expects($this->once())
            ->method('find')
            ->with($ticketId)
            ->willReturn(null);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(sprintf('Ticket #%d not found for done notification.', $ticketId));

        $this->loggerMock->expects($this->never())->method('info');

        ($this->handler)($message);
    }
}
