<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    public const TYPE_BODYPUMP = 'BodyPump';
    public const TYPE_SPINNING = 'Spinning';
    public const TYPE_CORE = 'Core';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxParticipants = null;

    #[ORM\Column(length: 50)]
    private ?string $type = self::TYPE_BODYPUMP; 

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Song::class, cascade: ['persist', 'remove'])]
    private Collection $playList;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->playList = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    public function getPlayList(): Collection
    {
        return $this->playList;
    }

    public function addSong(Song $song): static
    {
        if (!$this->playList->contains($song)) {
            $this->playList->add($song);
            $song->setActivity($this);
        }
        return $this;
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function isFull(): bool
    {
        return $this->bookings->count() >= $this->maxParticipants;
    }
}