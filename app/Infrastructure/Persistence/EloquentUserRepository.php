<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $user = User::find($id);

        return $user ? $this->toArray($user) : null;
    }

    public function create(array $data): array
    {
        $user = User::create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? bcrypt('password'),
        ]);

        return $this->toArray($user);
    }

    public function assignRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        $user->assignRole($role);
    }

    /** @return list<string> */
    public function getUserRoles(int $userId): array
    {
        $user = User::find($userId);
        if (! $user) {
            return [];
        }

        return $user->getRoleNames()->map(fn ($r) => (string) $r)->all();
    }

    public function getFirstUserIdWithRole(string $role): ?int
    {
        $id = User::role($role)->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
