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
        //Extracción de Query Parameters 
        $onlyFree = filter_var($request->query->get('onlyfree', true), FILTER_VALIDATE_BOOLEAN);
        $type = $request->query->get('type');
        $page = (int) $request->query->get('page', 1);
        $pageSize = (int) $request->query->get('page_size', 10);
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'desc');

        // Consulta a BBDD (Filtros y Paginación)
        $result = $this->activityRepository->findByFilter(
            $onlyFree,
            $type,
            $page,
            $pageSize,
            $sort,
            $order
        );

        // Mapeo a DTO
        $dtos = [];
        foreach ($result['data'] as $activity) {
            $clientsSigned = $activity->getBookings()->count();
            $dtos[] = new ActivityResponseDTO($activity, $clientsSigned);
        }

        //Construcción de respuesta 'ActivityList'
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