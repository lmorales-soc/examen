<?php

namespace App\Application\DTOs;

final readonly class DeactivateEmployeeDTO
{
    public function __construct(
        public int $employeeId,
    ) {
    }
}
