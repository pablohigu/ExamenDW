<?php

namespace App\Controller;

use App\DTO\Response\ActivityResponseDTO;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
class ActivityController extends AbstractController
{
    public function __construct(
        private readonly ActivityRepository $activityRepository
    ) {
    }

    #[Route('', name: 'get_activities', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // 1. Extracción de Query Parameters según YAML
        $onlyFree = filter_var($request->query->get('onlyfree', true), FILTER_VALIDATE_BOOLEAN);
        $type = $request->query->get('type');
        $page = (int) $request->query->get('page', 1);
        $pageSize = (int) $request->query->get('page_size', 10);
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'desc');

        // 2. Consulta a BBDD (Filtros y Paginación)
        $result = $this->activityRepository->findByFilter(
            $onlyFree,
            $type,
            $page,
            $pageSize,
            $sort,
            $order
        );

        // 3. Mapeo a DTOs
        $dtos = [];
        foreach ($result['data'] as $activity) {
            // Se calcula clientsSigned aquí o en el repositorio. 
            // Para rendimiento óptimo, el repositorio ya debería traer el count, 
            // pero por simplicidad usaremos la colección lazy-loaded aquí.
            $clientsSigned = $activity->getBookings()->count();
            $dtos[] = new ActivityResponseDTO($activity, $clientsSigned);
        }

        // 4. Construcción de respuesta 'ActivityList'
        return $this->json([
            'data' => $dtos,
            'meta' => [
                'page' => $page,
                'limit' => $pageSize,
                'total-items' => $result['total']
            ]
        ]);
    }
}