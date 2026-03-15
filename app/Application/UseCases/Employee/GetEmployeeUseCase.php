<?php

namespace App\Application\UseCases\Employee;

use App\Domain\Repositories\EmployeeRepositoryInterface;

final class GetEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function execute(int $id): ?array
    {
        return $this->employeeRepository->findById($id);
    }
}
