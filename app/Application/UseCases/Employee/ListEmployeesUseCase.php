<?php

namespace App\Application\UseCases\Employee;

use App\Domain\Repositories\EmployeeRepositoryInterface;

final class ListEmployeesUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    /**
     * @param bool $activeOnly
     * @param int|null $areaId
     * @return array<int, array>
     */
    public function execute(bool $activeOnly = true, ?int $areaId = null): array
    {
        return $this->employeeRepository->list($activeOnly, $areaId);
    }
}
