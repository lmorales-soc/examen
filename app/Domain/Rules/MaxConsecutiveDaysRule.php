<?php

namespace App\Domain\Rules;

use App\Domain\Exceptions\TooManyConsecutiveDaysException;
use DateTimeInterface;

/**
 * Regla de dominio: no se pueden solicitar más de 6 días consecutivos.
 *
 * Evalúa el rango de fechas (en días naturales) y lanza si se supera el máximo.
 */
final class MaxConsecutiveDaysRule
{
    private const MAX_CONSECUTIVE_DAYS = 6;

    /**
     * Valida que el rango [start, end] no supere 6 días consecutivos (inclusive).
     *
     * @throws TooManyConsecutiveDaysException cuando el rango supera el máximo
     */
    public function validate(DateTimeInterface $start, DateTimeInterface $end): void
    {
        $calendarDays = $this->countCalendarDays($start, $end);

        if ($calendarDays > self::MAX_CONSECUTIVE_DAYS) {
            throw new TooManyConsecutiveDaysException(
                $calendarDays,
                self::MAX_CONSECUTIVE_DAYS
            );
        }
    }

    /**
     * Comprueba si el rango cumple la regla (sin lanzar).
     */
    public function passes(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        return $this->countCalendarDays($start, $end) <= self::MAX_CONSECUTIVE_DAYS;
    }

    /**
     * Número de días naturales entre start y end (inclusive).
     */
    public function countCalendarDays(DateTimeInterface $start, DateTimeInterface $end): int
    {
        $start = \DateTimeImmutable::createFromInterface($start);
        $end = \DateTimeImmutable::createFromInterface($end);

        if ($start > $end) {
            return 0;
        }

        $diff = $start->diff($end);

        return $diff->days + 1;
    }

    public static function getMaxConsecutiveDays(): int
    {
        return self::MAX_CONSECUTIVE_DAYS;
    }
}
