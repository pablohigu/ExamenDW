<?php

declare(strict_types=1);

namespace App\DTO\Criteria;

class ActivityCriteria
{
    public function __construct(
        public readonly bool $onlyFree = true,
        public readonly ?string $type = null,
        public readonly int $page = 1,
        public readonly int $pageSize = 10,
        public readonly string $sort = 'date',
        public readonly string $order = 'desc'
    ) {
    }
}
