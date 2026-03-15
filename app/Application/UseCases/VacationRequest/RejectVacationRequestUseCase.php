<?php

namespace App\Application\UseCases\VacationRequest;

use App\Application\DTOs\RejectVacationRequestDTO;
use App\Application\Services\ApprovalResolverService;
use App\Domain\Repositories\VacationRequestRepositoryInterface;

final class RejectVacationRequestUseCase
{
    public function __construct(
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
        private readonly ApprovalResolverService $approvalResolver,
    ) {
    }

    /**
     * Rechaza la solicitud con motivo. Comprueba que quien rechaza sea el aprobador
     * según flujo (empleado → gerente área; gerente → RH). ADMIN puede siempre.
     *
     * @return array Solicitud rechazada
     */
    public function execute(RejectVacationRequestDTO $dto): array
    {
        $request = $this->vacationRequestRepository->findById($dto->requestId);
        if (! $request) {
            throw new \InvalidArgumentException("Solicitud no encontrada: {$dto->requestId}");
        }
        if (($request['status'] ?? '') !== 'pending') {
            throw new \InvalidArgumentException('Solo se pueden rechazar solicitudes pendientes.');
        }

        $this->approvalResolver->authorizeApproval($request, $dto->rejectedByUserId);

        return $this->vacationRequestRepository->updateStatus(
            $dto->requestId,
            'rejected',
            $dto->rejectedByUserId,
            $dto->rejectionReason
        );
    }
}
