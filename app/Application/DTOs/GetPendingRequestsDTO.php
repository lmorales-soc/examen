<?php

namespace App\Application\DTOs;

final readonly class GetPendingRequestsDTO
{
    public function __construct(
        public ?int $employeeId = null,
        public ?int $areaId = null,
        public ?int $assignedApproverId = null,
    ) {
    }
}
