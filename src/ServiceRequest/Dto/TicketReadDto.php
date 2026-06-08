<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Normalizer\PaginatedCollectionNormalizer;
use App\ServiceRequest\State\Provider\TicketCollectionProvider;
use App\ServiceRequest\State\Provider\TicketItemProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use ArrayObject;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new GetCollection(
            uriTemplate: '/tickets',
            openapi: new Operation(
                responses: [
                    '200' => new OpenApiResponse(description: 'Successful operation. Returns a paginated list of tickets.'),
                    '401' => new OpenApiResponse(
                        description: 'Unauthorized - JWT token not found or invalid.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: ['title' => 'An error occurred', 'status' => 401, 'detail' => 'JWT Token not found']
                            )
                        ])
                    )
                ],
                summary: 'Get a paginated list of tickets',
                description: 'Retrieves a list of service tickets with optional filtering and sorting.',
                parameters: [
                    new Parameter(
                        name: 'status',
                        in: 'query',
                        description: 'Exact match for the ticket status.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => TicketStatus::CHOICES
                        ]
                    ),
                    new Parameter(
                        name: 'priority',
                        in: 'query',
                        description: 'Exact match for the ticket priority.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => TicketPriority::CHOICES
                        ]
                    ),
                    new Parameter(
                        name: 'serialNumber',
                        in: 'query',
                        description: 'Partial match for the device serial number (LIKE %...%).',
                        required: false,
                        schema: ['type' => 'string']
                    ),
                    new Parameter(
                        name: 'order[createdAt]',
                        in: 'query',
                        description: 'Sorting direction by creation date.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => ['ASC', 'DESC']
                        ]
                    ),
                    new Parameter(
                        name: 'order[status]',
                        in: 'query',
                        description: 'Sorting direction by status.',
                        required: false,
                        schema: [
                            'type' => 'string',
                            'enum' => ['ASC', 'DESC']
                        ]
                    )
                ],
                extensionProperties: ['x-paginated-collection-json' => true],
            ),
            normalizationContext: [
                'groups' => ['ticket:read:list'],
                PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true
            ],
            provider: TicketCollectionProvider::class
        ),
        new Get(
            uriTemplate: '/tickets/{id}',
            openapi: new Operation(
                responses: [
                    '200' => new OpenApiResponse(description: 'Successful operation. Returns the ticket details.'),
                    '401' => new OpenApiResponse(
                        description: 'Unauthorized - JWT token not found or invalid.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: ['title' => 'An error occurred', 'status' => 401, 'detail' => 'Invalid JWT Token']
                            )
                        ])
                    ),
                    '404' => new OpenApiResponse(
                        description: 'Not Found - Ticket does not exist.',
                        content: new ArrayObject([
                            'application/problem+json' => new MediaType(
                                schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                                example: ['title' => 'An error occurred', 'status' => 404, 'detail' => 'Ticker with id 123 not found.']
                            )
                        ])
                    )
                ],
                summary: 'Get ticket details',
                description: 'Retrieves full details of a specific service ticket by ID.'
            ),
            normalizationContext: ['groups' => ['ticket:read:item']],
            provider: TicketItemProvider::class,
        )
    ],
    cacheHeaders: [
        'max_age' => 60,
        'shared_max_age' => 120,
        'vary' => ['Authorization']
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')"
)]
class TicketReadDto
{
    #[ApiProperty(identifier: true)]
    #[Groups(['ticket:read:list', 'ticket:read:item'])]
    public int $id;

    #[Groups(['ticket:read:list', 'ticket:read:item'])]
    public string $title;

    #[Groups(['ticket:read:list', 'ticket:read:item'])]
    public string $status;

    #[Groups(['ticket:read:list', 'ticket:read:item'])]
    public string $priority;

    #[Groups(['ticket:read:list', 'ticket:read:item'])]
    public string $createdAt;

    #[Groups(['ticket:read:item'])]
    public string $description;

    #[Groups(['ticket:read:item'])]
    public ?string $assignedTechnicianName = null;

    #[Groups(['ticket:read:item'])]
    public ?string $deviceSerialNumber;

    #[Groups(['ticket:read:item'])]
    public int $version;

    /**
     * @var string[]
     */
    #[Groups(['ticket:read:item'])]
    public array $availableTransitions = [];
}
