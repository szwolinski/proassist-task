<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\State\Processor\TicketCreateProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use ArrayObject;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new Post(
            uriTemplate: '/tickets',
            openapi: new OpenApiOperation(
                responses: [
                    '201' => new OpenApiResponse(
                        description: 'Ticket created successfully.'
                    ),
                    '400' => new OpenApiResponse(
                        description: 'Bad Request - Invalid input data.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 400,
                                    'detail' => 'Invalid data provided.'
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
                    '403' => new OpenApiResponse(
                        description: 'Forbidden - You do not have permission to create tickets.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 403,
                                    'detail' => 'You are not allowed to create tickets.'
                                ]
                            )
                        ])
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Not Found - Device does not exist.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Device not found.'
                                ]
                            )
                        ])
                    ),
                    '422' => new OpenApiResponse(
                        description: 'Unprocessable Entity - Validation error.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: [
                                    'title' => 'An error occurred',
                                    'status' => 422,
                                    'detail' => 'title: Title cannot be blank.',
                                    'violations' => [
                                        [
                                            'propertyPath' => 'title',
                                            'message' => 'Title cannot be blank.'
                                        ]
                                    ]
                                ]
                            )
                        ])
                    )
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
