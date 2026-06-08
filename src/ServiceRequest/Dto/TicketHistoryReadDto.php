<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\State\Provider\TicketHistoryProvider;
use ArrayObject;

#[ApiResource(
    shortName: 'TicketHistory',
    operations: [
        new GetCollection(
            uriTemplate: '/tickets/{id}/history',
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'Successful operation. Returns the history of the ticket.'
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
                    '404' => new OpenApiResponse(
                        description: 'Not Found - Ticket does not exist.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Ticket not found'
                                ]
                            )
                        ])
                    )
                ],
                summary: 'Get ticket history',
                description: 'Retrieves the history of status changes for a specific ticket.'
            ),
            paginationEnabled: false,
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
