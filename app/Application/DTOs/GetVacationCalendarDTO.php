<?php

namespace App\Application\DTOs;

final readonly class GetVacationCalendarDTO
{
    /**
     * @param  array<int>|null  $areaIds  IDs de áreas (ej. las que gerencia el usuario)
     */
    public function __construct(
        public int $year,
        public ?int $areaId = null,
        public ?int $employeeId = null,
        public ?array $areaIds = null,
    ) {
    }
}
