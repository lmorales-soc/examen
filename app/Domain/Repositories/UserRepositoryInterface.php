<?php

namespace App\Domain\Repositories;

interface UserRepositoryInterface
{
    public function findById(int $id): ?array;

    public function create(array $data): array;

    public function assignRole(int $userId, string $role): void;

    /** @return list<string> Nombres de roles del usuario */
    public function getUserRoles(int $userId): array;

    /** Primer usuario que tenga el rol indicado (para resolver aprobador HR). */
    public function getFirstUserIdWithRole(string $role): ?int;
}
