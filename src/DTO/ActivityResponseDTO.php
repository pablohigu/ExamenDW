<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Activity;
use Symfony\Component\Serializer\Annotation\SerializedName;

class ActivityResponseDTO
{
    public int $id;

    #[SerializedName('max_participants')]
    public int $maxParticipants;

    #[SerializedName('clients_signed')]
    public int $clientsSigned;

    public string $type;

    #[SerializedName('play_list')]
    public array $playList;

    #[SerializedName('date_start')]
    public string $dateStart;

    #[SerializedName('date_end')]
    public string $dateEnd;

    public function __construct(Activity $activity, int $clientsSigned = 0)
    {
        $this->id = $activity->getId();
        $this->maxParticipants = $activity->getMaxParticipants();
        $this->clientsSigned = $clientsSigned;
        $this->type = $activity->getType();
        $this->dateStart = $activity->getDateStart()->format(\DateTimeInterface::ATOM);
        $this->dateEnd = $activity->getDateEnd()->format(\DateTimeInterface::ATOM);
        
        $this->playList = [];
        foreach ($activity->getPlayList() as $song) {
            $this->playList[] = new SongResponseDTO($song);
        }
    }
}