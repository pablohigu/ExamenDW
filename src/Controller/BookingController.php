<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\BookingRequestDTO;
use App\DTO\Response\BookingResponseDTO;
use App\Entity\Booking;
use App\Repository\ActivityRepository;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/bookings')]
class BookingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly ActivityRepository $activityRepository,
        private readonly ClientRepository $clientRepository,
        private readonly BookingRepository $bookingRepository
    ) {
    }

    #[Route('', name: 'create_booking', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // 1. Deserialización JSON -> DTO [cite: 60]
        try {
            /** @var BookingRequestDTO $dto */
            $dto = $this->serializer->deserialize($request->getContent(), BookingRequestDTO::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['code' => 400, 'description' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Validación de formato de datos
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['code' => 400, 'description' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // 3. Obtención de Entidades [cite: 62]
        $activity = $this->activityRepository->find($dto->activityId);
        $client = $this->clientRepository->find($dto->clientId);

        if (!$activity || !$client) {
            return $this->json(['code' => 400, 'description' => 'Client or Activity not found'], Response::HTTP_BAD_REQUEST);
        }

        // 4. Validaciones de Negocio

        // A) Plazas suficientes [cite: 63]
        if ($activity->getBookings()->count() >= $activity->getMaxParticipants()) {
            return $this->json(['code' => 400, 'description' => 'No free places available'], Response::HTTP_BAD_REQUEST);
        }

        // B) Lógica de tipo de usuario (Standard vs Premium) [cite: 64-66]
        if ($client->getType() === 'standard') {
            // Contar reservas de la semana de la actividad objetivo
            $bookingsThisWeek = $this->bookingRepository->countBookingsForClientInWeek($client, $activity->getDateStart());
            
            if ($bookingsThisWeek >= 2) {
                return $this->json(['code' => 400, 'description' => 'Standard users limit reached (max 2/week)'], Response::HTTP_BAD_REQUEST);
            }
        }

        // 5. Persistencia
        $booking = new Booking();
        $booking->setActivity($activity);
        $booking->setClient($client);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        // 6. Respuesta [cite: 67]
        return $this->json(new BookingResponseDTO($booking));
    }
}