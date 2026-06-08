<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\State\Processor\TicketCreateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets',
            openapi: new OpenApiOperation(
                responses: [
                    '201' => new OpenApiResponse(description: 'Ticket created successfully'),
                    '400' => new OpenApiResponse(description: 'Invalid input data'),
                    '404' => new OpenApiResponse(description: 'Device not found')
                ],
                summary: 'Create a new ticket',
                description: 'Creates a new service ticket associated with a specific device.'
            ),
            input: self::class,
            output: TicketReadDto::class,
            processor: TicketCreateProcessor::class
        )
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')"
)]
class TicketCreatePayload
{
    #[Assert\NotBlank(message: 'Title cannot be blank.')]
    #[Assert\Length(min: 5, max: 255)]
    public string $title;

    #[Assert\NotBlank(message: 'Description cannot be blank.')]
    #[Assert\Length(min: 5, max: 1000)]
    public string $description;

    #[Assert\NotBlank(message: 'Priority cannot be blank.')]
    public TicketPriority $priority;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ApiProperty(description: 'ID of the device this ticket belongs to')]
    public int $deviceId;
}
