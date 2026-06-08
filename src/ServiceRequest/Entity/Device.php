<?php

declare(strict_types=1);

namespace App\ServiceRequest\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response;
use App\ServiceRequest\Repository\DeviceRepository;
use App\Shared\Normalizer\PaginatedCollectionNormalizer;
use ArrayObject;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(
            openapi: new Operation(
                extensionProperties: ['x-paginated-collection-json' => true]
            ),
            normalizationContext: [
                PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true
            ],
            parameters: [
                'model' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'model',
                    description: 'Search by partial model name'
                ),
                'serialNumber' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'serialNumber',
                    description: 'Search device by exact serial number'
                ),
                'order[createdAt]' => new QueryParameter(
                    filter: new OrderFilter(),
                    property: 'createdAt',
                    description: 'Sort by creation date (asc/desc)'
                ),
                'order[model]' => new QueryParameter(
                    filter: new OrderFilter(),
                    property: 'model',
                    description: 'Sort by model name (asc/desc)'
                )
            ]
        ),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    openapi: new OpenApiOperation(
        responses: [
            '401' => new Response(
                description: 'Invalid JWT Token.',
                content: new ArrayObject([
                    'application/problem+json' => new MediaType(
                        schema: new ArrayObject(['$ref' => '#/components/schemas/Error']),
                        example: [
                            'title' => 'An error occurred',
                            'status' => 401,
                            'detail' => 'JWT Token not found'
                        ]
                    )
                ])
            )
        ],
        summary: 'Get technician performance report',
        description: 'Returns aggregated statistics for each technician (total assigned vs completed tickets).'
    ),
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
)]
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $serialNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    private ?string $customerName = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): void
    {
        $this->serialNumber = $serialNumber;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): void
    {
        $this->customerName = $customerName;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
