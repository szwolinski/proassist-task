<?php

declare(strict_types=1);

namespace App\ServiceRequest\Message\Query;

use App\ServiceRequest\MessageHandler\Query\GetTicketsQueryHandler;

/**
 * @see GetTicketsQueryHandler
 */
final readonly class GetTicketsQuery
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $sort
    */
    public function __construct(
        public int $page,
        public int $limit,
        public array $filters,
        public array $sort
    ) {
    }
}
