<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;

// Clase contenedora para las estructuras de estadísticas anidadas del YAML
class StatisticsDTO
{
    
}

class StatisticsItemDTO 
{
    #[SerializedName('num_activities')]
    public string $numActivities;

    #[SerializedName('num_minutes')]
    public string $numMinutes;

    public function __construct(string $numActivities, string $numMinutes)
    {
        $this->numActivities = $numActivities;
        $this->numMinutes = $numMinutes;
    }
}

class StatisticsByTypeDTO
{
    public string $type;
    public array $statistics = []; 

    public function __construct(string $type, StatisticsItemDTO $stats)
    {
        $this->type = $type;
        $this->statistics[] = $stats;
    }
}

class StatisticsByYearDTO
{
    public int $year;
    
    #[SerializedName('statistics_by_type')]
    public array $statisticsByType = []; 

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function addStatByType(StatisticsByTypeDTO $stat): void
    {
        $this->statisticsByType[] = $stat;
    }
}