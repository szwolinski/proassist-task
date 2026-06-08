<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\ServiceRequest\State\Processor\TicketAssignProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets/{id}/assign',
            status: 200,
            openapi: new Operation(
                responses: [
                    '200' => new Response(description: 'Technician assigned successfully'),
                    '400' => new Response(description: 'Invalid input, technician inactive, or transition not allowed'),
                    '404' => new Response(description: 'Ticket or Technician not found')
                ],
                summary: 'Assign a technician to the ticket',
                description: 'Assigns an active technician to a specific ticket and changes its status to ASSIGNED.'
            ),
            input: self::class,
            processor: TicketAssignProcessor::class
        )
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')"
)]
class TicketAssignPayload
{
    #[ApiProperty(description: 'ID of the technician to assign')]
    #[Assert\NotBlank(message: 'Technician ID cannot be blank.')]
    #[Assert\Positive(message: 'Technician ID must be a valid number.')]
    public int $technicianId;

    #[Assert\NotBlank(message: 'Expected version cannot be blank.')]
    #[Assert\Positive(message: 'Expected version must be a valid number.')]
    public int $expectedVersion;
}
