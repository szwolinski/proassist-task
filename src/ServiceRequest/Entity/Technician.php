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
use App\ServiceRequest\Repository\TechnicianRepository;
use App\Shared\Normalizer\PaginatedCollectionNormalizer;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TechnicianRepository::class)]
#[ORM\Index(name: 'idx_tech_active', columns: ['active'])]
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
                'firstName' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'firstName',
                    description: 'Search by partial first name'
                ),
                'lastName' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'lastName',
                    description: 'Search by partial last name'
                ),
                'email' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'email',
                    description: 'Search by exact email address'
                ),
                'active' => new QueryParameter(
                    schema: ['type' => 'boolean'],
                    filter: new ExactFilter(),
                    property: 'active',
                    description: 'Filter by active status (true/false)',
                    castToNativeType: true
                ),
                'order[lastName]' => new QueryParameter(
                    filter: new OrderFilter(),
                    property: 'lastName',
                    description: 'Sort by last name (asc/desc)'
                ),
                'order[firstName]' => new QueryParameter(
                    filter: new OrderFilter(),
                    property: 'firstName',
                    description: 'Sort by first name (asc/desc)'
                ),
                'order[id]' => new QueryParameter(
                    filter: new OrderFilter(),
                    property: 'id',
                    description: 'Sort by ID (asc/desc)'
                )
            ]
        ),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
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
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 100,
    security: "is_granted('ROLE_ADMIN')"
)]
class Technician
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'First name cannot be blank.')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Last name cannot be blank.')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    private ?string $email = null;

    #[ORM\Column]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
