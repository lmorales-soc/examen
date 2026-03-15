<?php

namespace App\Domain\Repositories;

interface AreaRepositoryInterface
{
    public function findById(int $id): ?array;

    /** @return array<int, array> */
    public function list(bool $activeOnly = true): array;

    public function create(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): void;

    public function hasEmployees(int $areaId): bool;

    public function setAreaManager(int $areaId, int $userId): void;

    /** ID del usuario que es gerente del área, o null si no hay asignado. */
    public function getAreaManagerUserId(int $areaId): ?int;
}
