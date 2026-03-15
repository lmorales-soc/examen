<?php

namespace App\Domain\Rules;

use App\Domain\Exceptions\InsufficientVacationDaysException;

/**
 * Regla de dominio: no se pueden solicitar más días de los disponibles.
 *
 * Los días disponibles son el saldo actual del empleado (asignados - consumidos
 * en solicitudes ya aprobadas). Los días solo se descuentan al aprobar la solicitud.
 */
final class AvailableDaysRule
{
    /**
     * Valida que los días solicitados no superen los disponibles.
     *
     * @param int $requestedDays Días que se quieren solicitar (ya en días hábiles si aplica)
     * @param int $availableDays Días disponibles del empleado para el periodo
     * @throws InsufficientVacationDaysException cuando requested > available
     */
    public function validate(int $requestedDays, int $availableDays): void
    {
        if ($requestedDays <= 0) {
            return;
        }

        if ($requestedDays > $availableDays) {
            throw new InsufficientVacationDaysException($requestedDays, $availableDays);
        }
    }

    /**
     * Comprueba si la solicitud cumple la regla (sin lanzar).
     */
    public function passes(int $requestedDays, int $availableDays): bool
    {
        if ($requestedDays <= 0) {
            return true;
        }

        return $requestedDays <= $availableDays;
    }
}
