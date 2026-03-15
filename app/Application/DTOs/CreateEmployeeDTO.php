<?php

namespace App\Application\DTOs;

use DateTimeInterface;

/**
 * Datos para crear empleado y su usuario de acceso al sistema.
 * Incluye datos del usuario (name, email, password, role) y del empleado.
 */
final readonly class CreateEmployeeDTO
{
    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $userPassword,
        public string $role,
        public int $areaId,
        public string $employeeNumber,
        public string $firstName,
        public string $lastName,
        public DateTimeInterface $hireDate,
        public ?int $vacationDaysAnnual = null,
    ) {
    }
}
