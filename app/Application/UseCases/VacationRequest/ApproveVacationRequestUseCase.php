<?php

namespace App\Application\UseCases\VacationRequest;

use App\Application\DTOs\ApproveVacationRequestDTO;
use App\Application\Services\ApprovalResolverService;
use App\Domain\Repositories\VacationRequestRepositoryInterface;

final class ApproveVacationRequestUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
        private readonly ApprovalResolverService $approvalResolver,
    ) {
    }

    /**
     * Aprueba la solicitud. Comprueba que quien aprueba sea el aprobador según flujo
     * (empleado → gerente área; gerente → RH). ADMIN puede siempre.
     *
     * @return array Solicitud aprobada
     */
    public function execute(ApproveVacationRequestDTO $dto): array
    {
        $request = $this->vacationRequestRepository->findById($dto->requestId);
        if (! $request) {
            throw new \InvalidArgumentException("Solicitud no encontrada: {$dto->requestId}");
        }
        if (($request['status'] ?? '') !== 'pending') {
            throw new \InvalidArgumentException('Solo se pueden aprobar solicitudes pendientes.');
        }

        $this->approvalResolver->authorizeApproval($request, $dto->approvedByUserId);

        return $this->vacationRequestRepository->updateStatus(
            $dto->requestId,
            'approved',
            $dto->approvedByUserId,
            null
        );
    }
}
