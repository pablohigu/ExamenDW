<?php


namespace App\Controller;

use App\DTO\Response\ClientResponseDTO;
use App\DTO\Response\StatisticsByYearDTO;
use App\DTO\Response\StatisticsByTypeDTO;
use App\DTO\Response\StatisticsItemDTO;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/{id}', name: 'get_client', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        // 1. Obtener Cliente [cite: 85-87]
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return $this->json(['code' => 400, 'description' => 'Client not found'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Query Parameters [cite: 88-89]
        $withBookings = filter_var($request->query->get('with_bookings', false), FILTER_VALIDATE_BOOLEAN);
        $withStatistics = filter_var($request->query->get('with_statistics', false), FILTER_VALIDATE_BOOLEAN);

        $statsData = null;

        // 3. Cálculo de Estadísticas (Si se solicita) [cite: 91-92]
        if ($withStatistics) {
            $statsData = $this->calculateStatistics($client->getId());
        }

        // 4. Respuesta
        return $this->json(new ClientResponseDTO($client, $withBookings, $withStatistics, $statsData));
    }

    /**
     * Calcula estadísticas agrupadas por Año -> Tipo.
     * Realiza una consulta de agregación directa usando DBAL o DQL para rendimiento.
     */
    private function calculateStatistics(int $clientId): array
    {
        $conn = $this->entityManager->getConnection();

        // SQL Cruda para máxima eficiencia y agrupamiento complejo
        // Asumimos que la duración es (date_end - date_start) en minutos.
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

        $resultSet = $conn->executeQuery($sql, ['clientId' => $clientId])->fetchAllAssociative();

        // Estructurar datos para el DTO anidado
        $statsByYear = [];

        foreach ($resultSet as $row) {
            $year = (int)$row['year_val'];
            
            if (!isset($statsByYear[$year])) {
                $statsByYear[$year] = new StatisticsByYearDTO($year);
            }

            $statItem = new StatisticsItemDTO(
                (int)$row['num_activities'],
                (int)$row['num_minutes'] // [cite: 92]
            );

            $statByType = new StatisticsByTypeDTO($row['type_val'], $statItem);
            
            $statsByYear[$year]->addStatByType($statByType);
        }

        return array_values($statsByYear);
    }
}