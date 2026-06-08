<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Command;

use App\ServiceRequest\MessageHandler\Command\ChangeTicketStatusHandler;

/**
 * @see ChangeTicketStatusHandler
 */
final readonly class ChangeTicketStatusCommand
{
    public function __construct(
        public int $ticketId,
        public string $transition,
        public int $expectedVersion
    ) {
    }
}
