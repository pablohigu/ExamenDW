<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Client;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\Ignore;

class ClientResponseDTO
{
    public int $id;
    public string $type;
    public string $name;
    public string $email;

    #[SerializedName('activities_booked')]
    public ?array $activitiesBooked = null;

    #[SerializedName('activity_statistics')]
    public ?array $activityStatistics = null;

    public function __construct(Client $client, bool $includeBookings = false, bool $includeStats = false, ?array $statsData = null)
    {
        $this->id = $client->getId();
        $this->type = $client->getType();
        $this->name = $client->getName();
        $this->email = $client->getEmail();

        if ($includeBookings) {
            $this->activitiesBooked = [];
            foreach ($client->getBookings() as $booking) {
                $this->activitiesBooked[] = new BookingResponseDTO($booking);
            }
        }

        if ($includeStats && $statsData) {
            $this->activityStatistics = $statsData; 
        }
    }
}