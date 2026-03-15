<?php

namespace App\Domain\Exceptions;

/**
 * Se lanza cuando un usuario intenta aprobar o rechazar una solicitud
 * que no le corresponde según el flujo de aprobación (empleado → gerente área; gerente → RH).
 */
class UnauthorizedApprovalException extends DomainException
{
    public function __construct(
        int $requestId,
        int $userId,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "El usuario {$userId} no está autorizado para aprobar o rechazar la solicitud {$requestId}.",
            0,
            $previous
        );
    }
}
