<?php

declare(strict_types=1);

namespace App\ServiceRequest\Repository;

use App\ServiceRequest\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $sort
     * @return Paginator<Ticket>
     */
    public function getFilteredTickets(int $page, int $limit, array $filters, array $sort): Paginator
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.device', 'd')->addSelect('d')
            ->leftJoin('t.assignedTechnician', 'tech')->addSelect('tech');

        if (!empty($filters['status'])) {
            $qb->andWhere('t.status = :status')->setParameter('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $qb->andWhere('t.priority = :priority')->setParameter('priority', $filters['priority']);
        }
        if (!empty($filters['serialNumber'])) {
            $qb->andWhere('d.serialNumber LIKE :serial')
                ->setParameter('serial', '%' . $filters['serialNumber'] . '%');
        }

        $allowedSortFields = ['createdAt', 'updatedAt', 'title', 'status', 'priority'];
        foreach ($sort as $field => $direction) {
            if (in_array($field, $allowedSortFields, true)) {
                $qb->addOrderBy('t.' . $field, strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC');
            }
        }

        if (empty($sort)) {
            $qb->addOrderBy('t.createdAt', 'DESC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, fetchJoinCollection: true);
    }
}
