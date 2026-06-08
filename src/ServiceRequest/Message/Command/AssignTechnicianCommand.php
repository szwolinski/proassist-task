<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Command;

use App\ServiceRequest\MessageHandler\Command\AssignTechnicianHandler;

/**
 * @see AssignTechnicianHandler
 */
final readonly class AssignTechnicianCommand
{
    public function __construct(
        public int $ticketId,
        public int $technicianId,
        public int $expectedVersion,
    ) {
    }
}
