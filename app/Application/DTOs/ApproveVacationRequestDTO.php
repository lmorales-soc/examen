<?php

namespace App\Application\DTOs;

final readonly class ApproveVacationRequestDTO
{
    public function __construct(
        public int $requestId,
        public int $approvedByUserId,
    ) {
    }
}
