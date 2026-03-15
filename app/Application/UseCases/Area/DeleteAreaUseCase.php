<?php

namespace App\Application\UseCases\Area;

use App\Domain\Repositories\AreaRepositoryInterface;

final class DeleteAreaUseCase
{
    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    public function execute(int $id): void
    {
        if ($this->areaRepository->hasEmployees($id)) {
            throw new \InvalidArgumentException('No se puede eliminar un área que tiene empleados asignados.');
        }
        $this->areaRepository->delete($id);
    }
}
