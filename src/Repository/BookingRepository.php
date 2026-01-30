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

    /**
     * Cuenta cuántas reservas tiene un cliente en la misma semana que la fecha dada.
     * Útil para limitar reservas semanales (ej. usuarios Standard).
     */
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

    /**
     * Obtiene estadísticas agregadas (minutos totales, núm actividades) agrupadas por Año y Tipo.
     * Utiliza SQL nativo (DBAL) para realizar la agregación de forma eficiente.
     */
    public function getStatisticsForClient(int $clientId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                YEAR(a.date_start) as year_val,
                a.type as type_val,
                COUNT(b.id) as num_activities,
                SUM(TIMESTAMPDIFF(MINUTE, a.date_start, a.date_end)) as num_minutes
            FROM booking b
            JOIN activity a ON b.activity_id = a.id
            WHERE b.client_id = :clientId
            GROUP BY year_val, type_val
            ORDER BY year_val DESC, type_val ASC
        ';

        return $conn->executeQuery($sql, ['clientId' => $clientId])->fetchAllAssociative();
    }
}