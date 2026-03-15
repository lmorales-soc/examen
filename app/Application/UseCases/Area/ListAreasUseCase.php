<?php

namespace App\Application\UseCases\Area;

use App\Domain\Repositories\AreaRepositoryInterface;

final class ListAreasUseCase
{
    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    /**
     * @param bool $activeOnly
     * @return array<int, array>
     */
    public function execute(bool $activeOnly = true): array
    {
        return $this->areaRepository->list($activeOnly);
    }
}
