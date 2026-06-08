<?php

declare(strict_types=1);

namespace App\ServiceRequest\Repository;

use App\ServiceRequest\Entity\Technician;
use App\ServiceRequest\Entity\Ticket;
use App\ServiceRequest\Enum\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Technician>
 */
class TechnicianRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technician::class);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getPerformanceReport(): array
    {
        return $this->createQueryBuilder('tech')
            ->select('tech.id AS technicianId')
            ->addSelect('tech.firstName')
            ->addSelect('tech.lastName')
            ->addSelect('COUNT(t.id) AS totalAssigned')
            ->addSelect("SUM(CASE WHEN t.status = :doneStatus THEN 1 ELSE 0 END) AS totalCompleted")
            ->leftJoin(Ticket::class, 't', 'WITH', 't.assignedTechnician = tech')
            ->setParameter('doneStatus', TicketStatus::DONE->value)
            ->groupBy('tech.id')
            ->addOrderBy('tech.lastName', 'ASC')
            ->addOrderBy('tech.firstName', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
