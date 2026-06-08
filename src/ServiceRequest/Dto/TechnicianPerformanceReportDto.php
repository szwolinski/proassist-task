<?php

declare(strict_types=1);

namespace App\ServiceRequest\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\ServiceRequest\State\Provider\TechnicianPerformanceReportProvider;

#[ApiResource(
    shortName: 'Report',
    operations: [
        new GetCollection(
            uriTemplate: '/reports/technician-performance',
            openapi: new OpenApiOperation(
                summary: 'Get technician performance report',
                description: 'Returns aggregated statistics for each technician (total assigned vs completed tickets).'
            ),
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
