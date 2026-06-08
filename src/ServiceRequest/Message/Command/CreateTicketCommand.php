<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Command;

use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\MessageHandler\Command\CreateTicketHandler;

/**
 * @see CreateTicketHandler
 */
final readonly class CreateTicketCommand
{
    public function __construct(
        public string $title,
        public string $description,
        public TicketPriority $priority,
        public int $deviceId
    ) {
    }
}
