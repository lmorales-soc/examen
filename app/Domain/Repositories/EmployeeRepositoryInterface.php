<?php

namespace App\Domain\Repositories;

interface EmployeeRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByUserId(int $userId): ?array;

    public function findByEmployeeNumber(string $employeeNumber): ?array;

    /** @return array<int, array> */
    public function list(bool $activeOnly = true, ?int $areaId = null): array;

    public function create(array $data): array;

    public function update(int $id, array $data): array;

    public function deactivate(int $id): void;

    public function getAvailableVacationDays(int $employeeId, int $year): int;
}
