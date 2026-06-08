<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Query;

use App\ServiceRequest\MessageHandler\Query\GetTicketHistoryQueryHandler;

/**
 * @see GetTicketHistoryQueryHandler
 */
final readonly class GetTicketHistoryQuery
{
    public function __construct(public int $id)
    {
    }
}
