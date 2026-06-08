<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\State\Processor\TicketAssignProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use ArrayObject;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets/{id}/assign',
            status: 204,
            openapi: new Operation(
                responses: [
                    '204' => new Response(
                        description: 'Technician assigned successfully (No Content).'
                    ),
                    '400' => new Response(
                        description: 'Bad Request - Technician inactive or transition not allowed.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 400,
                                    'detail' => 'Technician not found or is inactive.'
                                ]
                            )
                        ])
                    ),
                    '401' => new Response(
                        description: 'Unauthorized - Invalid or expired JWT Token.',
                        content: new ArrayObject([
                            'application/json' => new MediaType(
                                schema: new ArrayObject([
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => ['type' => 'integer', 'example' => 401],
                                        'message' => ['type' => 'string', 'example' => 'Invalid JWT Token']
                                    ]
                                ]),
                                example: [
                                    'code' => 401,
                                    'message' => 'Invalid JWT Token'
                                ]
                            )
                        ])
                    ),
                    '403' => new Response(
                        description: 'Forbidden - You do not have permission to assign technicians.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 403,
                                    'detail' => 'You do not have permission to assign technicians to this ticket.'
                                ]
                            )
                        ])
                    ),
                    '404' => new Response(
                        description: 'Not Found - Ticket does not exist.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Ticket not found.'
                                ]
                            )
                        ])
                    ),
                    '409' => new Response(
                        description: 'Conflict - Ticket was modified by someone else (Optimistic Lock failure).',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 409,
                                    'detail' => 'Ticket was modified by someone else.'
                                ]
                            )
                        ])
                    ),
                    '422' => new Response(
                        description: 'Unprocessable Entity - Validation error.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 422,
                                    'detail' => 'technicianId: Technician ID cannot be blank.'
                                ]
                            )
                        ])
                    )
                ],
                summary: 'Assign a technician to the ticket',
                description: 'Assigns an active technician to a specific ticket and changes its status to ASSIGNED.'
            ),
            input: self::class,
            output: false,
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
