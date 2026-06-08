<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message;

use App\ServiceRequest\MessageHandler\TicketDoneHandler;

/**
 * @see TicketDoneHandler
 */
final readonly class TicketDoneMessage
{
    public function __construct(public int $ticketId)
    {
    }
}
