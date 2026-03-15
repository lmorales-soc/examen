<?php

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\AssignAreaManagerDTO;
use App\Domain\Repositories\AreaRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class AssignAreaManagerUseCase
{
    private const AREA_MANAGER_ROLE = 'AREA_MANAGER';

    public function __construct(
        private readonly AreaRepositoryInterface $areaRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * Asigna un usuario como gestor del área (rol + vínculo en área).
     */
    public function execute(AssignAreaManagerDTO $dto): void
    {
        $area = $this->areaRepository->findById($dto->areaId);
        if (! $area) {
            throw new \InvalidArgumentException("Área no encontrada: {$dto->areaId}");
        }

        $this->userRepository->assignRole($dto->userId, self::AREA_MANAGER_ROLE);
        $this->areaRepository->setAreaManager($dto->areaId, $dto->userId);
    }
}
