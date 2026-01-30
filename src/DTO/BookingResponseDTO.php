<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Booking;
use Symfony\Component\Serializer\Annotation\SerializedName;

class BookingResponseDTO
{
    public int $id;

    public ActivityResponseDTO $activity;

    #[SerializedName('client_id')]
    public int $clientId;

    public function __construct(Booking $booking)
    {
        $this->id = $booking->getId();
        // Nota: Para clientesSigned pasamos el count real de la entidad o 0 si no se ha cargado explícitamente en este contexto
        $this->activity = new ActivityResponseDTO($booking->getActivity(), $booking->getActivity()->getBookings()->count());
        $this->clientId = $booking->getClient()->getId();
    }
}