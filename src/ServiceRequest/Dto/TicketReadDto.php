<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\ServiceRequest\Enum\TicketPriority;
use App\ServiceRequest\Enum\TicketStatus;
use App\ServiceRequest\Normalizer\PaginatedCollectionNormalizer;
use App\ServiceRequest\State\Provider\TicketCollectionProvider;
use App\ServiceRequest\State\Provider\TicketItemProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'Ticket',
    operations: [
        new GetCollection(
            uriTemplate: '/tickets',
            openapi: new Operation(
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
