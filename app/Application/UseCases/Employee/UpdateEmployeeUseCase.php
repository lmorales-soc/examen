<?php

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\UpdateEmployeeDTO;
use App\Domain\Repositories\EmployeeRepositoryInterface;

final class UpdateEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    /**
     * Actualiza datos del empleado. Solo se envían campos presentes en el DTO.
     *
     * @return array Empleado actualizado
     */
    public function execute(UpdateEmployeeDTO $dto): array
    {
        $employee = $this->employeeRepository->findById($dto->id);
        if (! $employee) {
            throw new \InvalidArgumentException("Empleado no encontrado: {$dto->id}");
        }

        $data = array_filter([
            'area_id' => $dto->areaId,
            'employee_number' => $dto->employeeNumber,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'hire_date' => $dto->hireDate?->format('Y-m-d'),
            'vacation_days_annual' => $dto->vacationDaysAnnual,
        ], fn ($v) => $v !== null);

        if (empty($data)) {
            return $employee;
        }

        return $this->employeeRepository->update($dto->id, $data);
    }
}
