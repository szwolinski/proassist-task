<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\ServiceRequest\State\Provider\TicketHistoryProvider;

#[ApiResource(
    shortName: 'TicketHistory',
    operations: [
        new GetCollection(
            uriTemplate: '/tickets/{id}/history',
            provider: TicketHistoryProvider::class
        )
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
)]
class TicketHistoryReadDto
{
    #[ApiProperty(identifier: true)]
    public int $id;

    public int $ticketId;

    public string $oldStatus;

    public string $newStatus;

    public string $changedAt;

    public string $changedBy;
}
