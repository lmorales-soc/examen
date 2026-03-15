<?php

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\CreateEmployeeDTO;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Services\VacationDaysCalculator;

final class CreateEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly VacationDaysCalculator $vacationDaysCalculator,
    ) {
    }

    /**
     * Crea el usuario de acceso al sistema, asigna el rol y crea el registro del empleado.
     * Así el empleado puede iniciar sesión con el rol correspondiente.
     *
     * @return array Empleado creado (incluye datos del usuario vinculado)
     */
    public function execute(CreateEmployeeDTO $dto): array
    {
        $user = $this->userRepository->create([
            'name' => $dto->userName,
            'email' => $dto->userEmail,
            'password' => $dto->userPassword,
        ]);

        $this->userRepository->assignRole((int) $user['id'], $dto->role);

        $referenceDate = new \DateTimeImmutable();
        $yearsOfService = VacationDaysCalculator::yearsOfService($dto->hireDate, $referenceDate);
        $vacationDaysAnnual = $dto->vacationDaysAnnual ?? $this->vacationDaysCalculator->getAnnualDaysBySeniority($yearsOfService);

        $employee = $this->employeeRepository->create([
            'user_id' => (int) $user['id'],
            'area_id' => $dto->areaId,
            'employee_number' => $dto->employeeNumber,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'hire_date' => $dto->hireDate->format('Y-m-d'),
            'vacation_days_annual' => $vacationDaysAnnual,
        ]);

        return $employee;
    }
}
