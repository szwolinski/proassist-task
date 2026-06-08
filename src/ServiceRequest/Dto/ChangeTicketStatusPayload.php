<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\State\Processor\TicketStatusChangeProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use ArrayObject;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets/{id}/status',
            status: 204,
            openapi: new OpenApiOperation(
                responses: [
                    '204' => new Response(
                        description: 'Status change accepted successfully.'
                    ),
                    '400' => new Response(
                        description: 'Bad Request - Invalid transition requested for the current state.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 400,
                                    'detail' => 'Cannot transition ticket to "finish".'
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
                        description: 'Forbidden - You do not have permission to edit this ticket.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 403,
                                    'detail' => 'You do not have permission to edit this ticket.'
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
                                    'detail' => 'transition: Transition cannot be blank.',
                                    'violations' => [
                                        [
                                            'propertyPath' => 'transition',
                                            'message' => 'Transition cannot be blank.'
                                        ]
                                    ]
                                ]
                            )
                        ])
                    )
                ],
                summary: 'Change Ticket Status',
                description: 'Change Ticket Status as Technician'
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
