<?php

namespace App\Domain\Rules;

use DateTimeInterface;

/**
 * Regla de dominio: las vacaciones no deben contar sábados ni domingos.
 *
 * Calcula el número de días hábiles (lunes a viernes) en un rango de fechas.
 * Usado para saber cuántos días de vacaciones consume una solicitud.
 */
final class WeekendExclusionRule
{
    /**
     * Cuenta solo días de lunes a viernes en el rango [start, end] inclusive.
     */
    public function countBusinessDays(DateTimeInterface $start, DateTimeInterface $end): int
    {
        $start = $this->toMutable($start);
        $end = $this->toMutable($end);

        if ($start > $end) {
            return 0;
        }

        $count = 0;
        $current = clone $start;
        $endDate = $end->format('Y-m-d');

        while ($current->format('Y-m-d') <= $endDate) {
            $dayOfWeek = (int) $current->format('N'); // 1 = Monday, 7 = Sunday
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $count++;
            }
            $current->modify('+1 day');
        }

        return $count;
    }

    /**
     * Indica si una fecha es fin de semana (sábado o domingo).
     */
    public function isWeekend(DateTimeInterface $date): bool
    {
        $dayOfWeek = (int) (new \DateTimeImmutable($date->format('c')))->format('N');
        return $dayOfWeek === 6 || $dayOfWeek === 7;
    }

    private function toMutable(DateTimeInterface $date): \DateTime
    {
        if ($date instanceof \DateTime) {
            return clone $date;
        }
        return \DateTime::createFromInterface($date);
    }
}
