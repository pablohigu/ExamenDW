<?php

declare(strict_types=1);

namespace App\DTO;

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
        $this->activity = new ActivityResponseDTO($booking->getActivity(), $booking->getActivity()->getBookings()->count());
        $this->clientId = $booking->getClient()->getId();
    }
}