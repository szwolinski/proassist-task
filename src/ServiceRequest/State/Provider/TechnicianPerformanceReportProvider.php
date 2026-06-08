<?php

declare(strict_types=1);

namespace App\ServiceRequest\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ServiceRequest\Dto\TechnicianPerformanceReportDto;
use App\ServiceRequest\Message\Query\GetTechnicianPerformanceReportQuery;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\HandleTrait;

/**
 * @implements ProviderInterface<TechnicianPerformanceReportDto>
 */
final class TechnicianPerformanceReportProvider implements ProviderInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * @return TechnicianPerformanceReportDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $reportData = $this->handle(new GetTechnicianPerformanceReportQuery());

        $dtos = [];
        foreach ($reportData as $row) {
            $dto = new TechnicianPerformanceReportDto();
            $dto->technicianId = $row['technicianId'];
            $dto->technicianName = sprintf('%s %s', $row['firstName'], $row['lastName']);
            $dto->totalAssigned = (int) $row['totalAssigned'];
            $dto->totalCompleted = (int) $row['totalCompleted'];
            $dtos[] = $dto;
        }

        return $dtos;
    }
}
