<?php


namespace App\Controller;

require_once __DIR__ . '/../DTO/StatisticsDTO.php';

use App\DTO\ClientResponseDTO;
use App\DTO\StatisticsByYearDTO;
use App\DTO\StatisticsByTypeDTO;
use App\DTO\StatisticsItemDTO;
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
        private readonly \App\Repository\BookingRepository $bookingRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/{id}', name: 'get_client', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        // Obtener Cliente 
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return $this->json(['code' => 400, 'description' => 'Client not found'], Response::HTTP_BAD_REQUEST);
        }

        // Query Parameters 
        $withBookings = filter_var($request->query->get('with_bookings', false), FILTER_VALIDATE_BOOLEAN);
        $withStatistics = filter_var($request->query->get('with_statistics', false), FILTER_VALIDATE_BOOLEAN);

        $statsData = null;

        // Cálculo de Estadísticas (Si se solicita) 
        if ($withStatistics) {
            $statsData = $this->calculateStatistics($client->getId());
        }

        // 4. Respuesta
        return $this->json(new ClientResponseDTO($client, $withBookings, $withStatistics, $statsData));
    }

    /**
     * Calcula estadísticas agrupadas por Año -> Tipo.
     */
    private function calculateStatistics(int $clientId): array
    {
        // Usar el método del repositorio en lugar de SQL raw en el controlador
        $resultSet = $this->bookingRepository->getStatisticsForClient($clientId);

        // Estructurar datos para el DTO anidado
        $statsByYear = [];

        foreach ($resultSet as $row) {
            $year = (int)$row['year_val'];
            
            if (!isset($statsByYear[$year])) {
                $statsByYear[$year] = new StatisticsByYearDTO($year);
            }

            $statItem = new StatisticsItemDTO(
                (string)$row['num_activities'],
                (string)$row['num_minutes'] 
            );

            $statByType = new StatisticsByTypeDTO($row['type_val'], $statItem);
            
            $statsByYear[$year]->addStatByType($statByType);
        }

        return array_values($statsByYear);
    }
}