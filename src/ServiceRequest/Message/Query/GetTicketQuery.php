<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Query;

use App\ServiceRequest\MessageHandler\Query\GetTicketQueryHandler;

/**
 * @see GetTicketQueryHandler
 */
final readonly class GetTicketQuery
{
    public function __construct(public int $id) {}
}
