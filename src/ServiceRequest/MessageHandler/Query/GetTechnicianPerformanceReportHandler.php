<?php

declare(strict_types=1);

namespace App\ServiceRequest\MessageHandler\Query;

use App\ServiceRequest\Message\Query\GetTechnicianPerformanceReportQuery;
use App\ServiceRequest\Repository\TechnicianRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetTechnicianPerformanceReportHandler
{
    public function __construct(private TechnicianRepository $technicianRepository) {}

    /**
     * @return array<array-key, mixed>
     */
    public function __invoke(GetTechnicianPerformanceReportQuery $query): array
    {
        return $this->technicianRepository->getPerformanceReport();
    }
}
