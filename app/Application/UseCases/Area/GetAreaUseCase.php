<?php

namespace App\Application\UseCases\Area;

use App\Domain\Repositories\AreaRepositoryInterface;

final class GetAreaUseCase
{
    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    public function execute(int $id): ?array
    {
        return $this->areaRepository->findById($id);
    }
}
