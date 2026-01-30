<?php
declare(strict_types=1);

namespace App\DTO;

use App\Entity\Song;
use Symfony\Component\Serializer\Annotation\SerializedName;

class SongResponseDTO
{
    public int $id;
    public string $name;

    #[SerializedName('duration_seconds')]
    public int $durationSeconds;

    public function __construct(Song $song)
    {
        $this->id = $song->getId();
        $this->name = $song->getName();
        $this->durationSeconds = $song->getDurationSeconds();
    }
}