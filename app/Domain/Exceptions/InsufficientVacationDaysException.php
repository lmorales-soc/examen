<?php

namespace App\Domain\Exceptions;

/**
 * Se lanza cuando se solicitan más días de vacaciones de los disponibles.
 */
class InsufficientVacationDaysException extends DomainException
{
    public function __construct(
        int $requested,
        int $available,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "Se solicitaron {$requested} días pero solo hay {$available} disponibles.",
            0,
            $previous
        );
    }
}
