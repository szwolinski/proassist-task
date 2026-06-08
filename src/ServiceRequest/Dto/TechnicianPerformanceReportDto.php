<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\MediaType;
use App\ServiceRequest\State\Provider\TechnicianPerformanceReportProvider;
use ArrayObject;

#[ApiResource(
    shortName: 'Report',
    operations: [
        new GetCollection(
            uriTemplate: '/reports/technician-performance',
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new Response(
                        description: 'Successful operation. Returns the report.'
                    ),
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
            paginationEnabled: false,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            provider: TechnicianPerformanceReportProvider::class
        )
    ]
)]
class TechnicianPerformanceReportDto
{
    #[ApiProperty(identifier: true)]
    public int $technicianId;

    public string $technicianName;

    #[ApiProperty(description: 'Total number of tickets assigned to this technician')]
    public int $totalAssigned;

    #[ApiProperty(description: 'Number of tickets successfully resolved (status: DONE)')]
    public int $totalCompleted;
}
