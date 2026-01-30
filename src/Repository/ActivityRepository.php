<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @return array{data: Activity[], total: int}
     */
    public function findByCriteria(\App\DTO\Criteria\ActivityCriteria $criteria): array {
        $qb = $this->createQueryBuilder('a');

        // Filtro por tipo
        if ($criteria->type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $criteria->type);
        }

        // Filtro onlyfree (AND lógico si ya hay filtro de tipo)
        if ($criteria->onlyFree) {
            // Subquery o Join para contar bookings
            $qb->leftJoin('a.bookings', 'b')
               ->groupBy('a.id')
               ->having('COUNT(b.id) < a.maxParticipants');
        }

        // Ordenación
        $sortField = match ($criteria->sort) {
            'date' => 'a.dateStart',
            default => 'a.dateStart',
        };
        $qb->orderBy($sortField, strtoupper($criteria->order));

        // Paginación
        $qb->setFirstResult(($criteria->page - 1) * $criteria->pageSize)
           ->setMaxResults($criteria->pageSize);

        $paginator = new Paginator($qb, true);

        return [
            'data' => iterator_to_array($paginator),
            'total' => count($paginator)
        ];
    }
}