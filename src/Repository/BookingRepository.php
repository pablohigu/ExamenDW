<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function countBookingsForClientInWeek(Client $client, \DateTimeInterface $date): int
    {
        // Calcular inicio (Lunes) y fin (Domingo) de la semana de la actividad
        $startOfWeek = (clone $date)->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek = (clone $date)->modify('sunday this week')->setTime(23, 59, 59);

        return $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->join('b.activity', 'a')
            ->where('b.client = :client')
            ->andWhere('a.dateStart BETWEEN :start AND :end')
            ->setParameter('client', $client)
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->getQuery()
            ->getSingleScalarResult();
    }
}