<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\AreaRepositoryInterface;
use App\Models\Area;

final class EloquentAreaRepository implements AreaRepositoryInterface
{
    /** @return array<int, array> */
    public function list(bool $activeOnly = true): array
    {
        $query = Area::query()->orderBy('name');
        $areas = $query->get();

        $result = [];
        foreach ($areas as $area) {
            $result[$area->id] = $this->toArray($area);
        }

        return $result;
    }

    public function findById(int $id): ?array
    {
        $area = Area::find($id);

        return $area ? $this->toArray($area) : null;
    }

    public function setAreaManager(int $areaId, int $userId): void
    {
        Area::where('id', $areaId)->update(['manager_user_id' => $userId]);
    }

    public function getAreaManagerUserId(int $areaId): ?int
    {
        $area = Area::find($areaId);

        return $area && $area->manager_user_id ? (int) $area->manager_user_id : null;
    }

    public function create(array $data): array
    {
        $area = Area::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            'manager_user_id' => $data['manager_user_id'] ?? null,
        ]);

        return $this->toArray($area);
    }

    public function update(int $id, array $data): array
    {
        $area = Area::findOrFail($id);
        $payload = [
            'name' => $data['name'] ?? $area->name,
            'slug' => $data['slug'] ?? $area->slug,
        ];
        if (array_key_exists('manager_user_id', $data)) {
            $payload['manager_user_id'] = $data['manager_user_id'];
        }
        $area->update($payload);

        return $this->toArray($area->fresh());
    }

    public function delete(int $id): void
    {
        Area::findOrFail($id)->delete();
    }

    public function hasEmployees(int $areaId): bool
    {
        return Area::where('id', $areaId)->has('employees')->exists();
    }

    private function toArray(Area $area): array
    {
        return [
            'id' => $area->id,
            'name' => $area->name,
            'slug' => $area->slug,
            'manager_user_id' => $area->manager_user_id,
        ];
    }
}
