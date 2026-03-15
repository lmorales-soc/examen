<?php

namespace App\Application\DTOs;

use DateTimeInterface;

final readonly class UpdateEmployeeDTO
{
    public function __construct(
        public int $id,
        public ?int $areaId = null,
        public ?string $employeeNumber = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?DateTimeInterface $hireDate = null,
        public ?int $vacationDaysAnnual = null,
    ) {
    }
}
