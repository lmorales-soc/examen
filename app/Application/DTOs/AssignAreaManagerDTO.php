<?php

namespace App\Application\DTOs;

final readonly class AssignAreaManagerDTO
{
    public function __construct(
        public int $areaId,
        public int $userId,
    ) {
    }
}
