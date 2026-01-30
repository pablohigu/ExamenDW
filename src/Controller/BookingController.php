<?php


namespace App\Controller;

use App\DTO\Request\BookingRequestDTO;
use App\DTO\Response\BookingResponseDTO;
use App\Entity\Booking;
use App\Repository\ActivityRepository;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use App\Entity\Client;
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
        // Deserialización JSON
        try {
            /** @var BookingRequestDTO $dto */
            $dto = $this->serializer->deserialize($request->getContent(), BookingRequestDTO::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['code' => 400, 'description' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Validación de formato de datos
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['code' => 400, 'description' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Obtención de Entidades
        $activity = $this->activityRepository->find($dto->activityId);
        $client = $this->clientRepository->find($dto->clientId);

        if (!$activity || !$client) {
            return $this->json(['code' => 400, 'description' => 'Client or Activity not found'], Response::HTTP_BAD_REQUEST);
        }

        // Validaciones de Negocio

        // Plazas suficientes
        if ($activity->isFull()) {
            return $this->json(['code' => 400, 'description' => 'No free places available'], Response::HTTP_BAD_REQUEST);
        }

        if ($client->isStandard()) {
           
            $bookingsThisWeek = $this->bookingRepository->countBookingsForClientInWeek($client, $activity->getDateStart());
            
            if ($bookingsThisWeek >= Client::MAX_BOOKINGS_STANDARD) {
                return $this->json(['code' => 400, 'description' => 'Standard users limit reached (max 2/week)'], Response::HTTP_BAD_REQUEST);
            }
        }
        $booking = new Booking();
        $booking->setActivity($activity);
        $booking->setClient($client);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $this->json(new BookingResponseDTO($booking));
    }
}