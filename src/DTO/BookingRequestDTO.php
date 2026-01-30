<?php
declare(strict_types=1);
namespace App\DTO\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class BookingRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[SerializedName('activity_id')]
    public ?int $activityId = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[SerializedName('client_id')]
    public ?int $clientId = null;
}