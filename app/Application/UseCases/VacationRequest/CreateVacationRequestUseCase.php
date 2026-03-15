<?php

namespace App\Application\UseCases\VacationRequest;

use App\Application\DTOs\CreateVacationRequestDTO;
use App\Application\Services\ApprovalResolverService;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Domain\Repositories\VacationRequestRepositoryInterface;
use App\Domain\Rules\AvailableDaysRule;
use App\Domain\Rules\MaxConsecutiveDaysRule;
use App\Domain\Services\VacationDaysCalculator;

final class CreateVacationRequestUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly VacationDaysCalculator $vacationDaysCalculator,
        private readonly MaxConsecutiveDaysRule $maxConsecutiveDaysRule,
        private readonly AvailableDaysRule $availableDaysRule,
        private readonly ApprovalResolverService $approvalResolver,
    ) {
    }

    /**
     * Crea una solicitud de vacaciones (pendiente). Valida reglas de negocio.
     * Los días se descuentan solo al aprobar.
     *
     * @return array Solicitud creada
     */
    public function execute(CreateVacationRequestDTO $dto): array
    {
        $this->maxConsecutiveDaysRule->validate($dto->startDate, $dto->endDate);

        $requestedDays = $this->vacationDaysCalculator->getBusinessDaysInRange($dto->startDate, $dto->endDate);
        if ($requestedDays <= 0) {
            throw new \InvalidArgumentException('El rango de fechas no contiene días hábiles.');
        }

        $year = (int) (new \DateTimeImmutable($dto->startDate->format('c')))->format('Y');
        $availableDays = $this->employeeRepository->getAvailableVacationDays($dto->employeeId, $year);
        $this->availableDaysRule->validate($requestedDays, $availableDays);

        $employee = $this->employeeRepository->findById($dto->employeeId);
        if (! $employee) {
            throw new \InvalidArgumentException("Empleado no encontrado: {$dto->employeeId}");
        }

        $requestPayload = [
            'employee_id' => $dto->employeeId,
            'start_date' => $dto->startDate->format('Y-m-d'),
            'end_date' => $dto->endDate->format('Y-m-d'),
            'days_requested' => $requestedDays,
            'status' => 'pending',
            'comments' => $dto->comments,
        ];

        $assignedApproverId = $this->approvalResolver->getApproverUserIdForRequest($requestPayload);
        if ($assignedApproverId !== null) {
            $requestPayload['assigned_approver_id'] = $assignedApproverId;
        }

        return $this->vacationRequestRepository->create($requestPayload);
    }
}
