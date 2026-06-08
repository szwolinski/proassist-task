<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\ServiceRequest\State\Processor\TicketStatusChangeProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets/{id}/status',
            status: 202,
            openapi: new OpenApiOperation(
                summary: 'Change Ticket Status',
                description: 'Change Ticket Status as Technician',
            ),
            input: ChangeTicketStatusPayload::class,
            output: false,
            processor: TicketStatusChangeProcessor::class
        ),
    ]
)]
final class ChangeTicketStatusPayload
{
    #[Assert\NotBlank(message: 'Transition cannot be blank.')]
    public string $transition;

    #[Assert\NotBlank(message: 'Expected version cannot be blank.')]
    #[Assert\Positive(message: 'Expected version must be a valid number.')]
    public int $expectedVersion;
}
