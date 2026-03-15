<?php

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\DeactivateEmployeeDTO;
use App\Domain\Repositories\EmployeeRepositoryInterface;

final class DeactivateEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    /**
     * Desactiva un empleado por ID.
     */
    public function execute(DeactivateEmployeeDTO $dto): void
    {
        $employee = $this->employeeRepository->findById($dto->employeeId);
        if (! $employee) {
            throw new \InvalidArgumentException("Empleado no encontrado: {$dto->employeeId}");
        }

        $this->employeeRepository->deactivate($dto->employeeId);
    }
}
