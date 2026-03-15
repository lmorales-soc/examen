<?php

namespace App\Application\UseCases\Area;

use App\Domain\Repositories\AreaRepositoryInterface;
use Illuminate\Support\Str;

final class UpdateAreaUseCase
{
    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    /**
     * @param int $id
     * @param array{name?: string, slug?: string, manager_user_id?: int|null} $data
     * @return array
     */
    public function execute(int $id, array $data): array
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->areaRepository->update($id, $data);
    }
}
