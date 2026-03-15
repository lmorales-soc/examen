<?php

namespace App\Application\DTOs;

final readonly class RejectVacationRequestDTO
{
    public function __construct(
        public int $requestId,
        public int $rejectedByUserId,
        public string $rejectionReason,
    ) {
    }
}
