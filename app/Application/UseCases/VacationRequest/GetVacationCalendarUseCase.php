<?php

namespace App\Application\UseCases\VacationRequest;

use App\Application\DTOs\GetVacationCalendarDTO;
use App\Domain\Repositories\VacationRequestRepositoryInterface;

final class GetVacationCalendarUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
    ) {
    }

    /**
     * Obtiene las vacaciones aprobadas para un año (y opcionalmente un área) para el calendario.
     *
     * @return array<int, array> Lista de periodos de vacaciones con fechas y datos para calendario
     */
    public function execute(GetVacationCalendarDTO $dto): array
    {
        return $this->vacationRequestRepository->getForCalendar(
            $dto->year,
            $dto->areaId,
            $dto->employeeId,
            $dto->areaIds
        );
    }
}
