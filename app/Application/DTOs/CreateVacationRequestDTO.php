<?php

namespace App\Application\DTOs;

use DateTimeInterface;

final readonly class CreateVacationRequestDTO
{
    public function __construct(
        public int $employeeId,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?string $comments = null,
    ) {
    }
}
