<?php

namespace App\Domain\Exceptions;

/**
 * Se lanza cuando se solicitan más de 6 días consecutivos.
 */
class TooManyConsecutiveDaysException extends DomainException
{
    public function __construct(
        int $requestedDays,
        int $maxAllowed = 6,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "No se pueden solicitar más de {$maxAllowed} días consecutivos. Solicitados: {$requestedDays}.",
            0,
            $previous
        );
    }
}
