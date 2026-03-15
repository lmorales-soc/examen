<?php

namespace App\Application\UseCases\VacationRequest;

use App\Application\DTOs\GetPendingRequestsDTO;
use App\Domain\Repositories\VacationRequestRepositoryInterface;

final class GetPendingRequestsUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
    ) {
    }

    /**
     * Lista solicitudes pendientes, opcionalmente filtradas por empleado o área.
     *
     * @return array<int, array>
     */
    public function execute(GetPendingRequestsDTO $dto): array
    {
        return $this->vacationRequestRepository->getPending($dto->employeeId, $dto->areaId, $dto->assignedApproverId);
    }
}
