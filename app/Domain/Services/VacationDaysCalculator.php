<?php

namespace App\Domain\Services;

use App\Domain\Rules\WeekendExclusionRule;
use DateTimeInterface;

/**
 * Calcula días de vacaciones según reglas de negocio.
 *
 * - Días anuales por antigüedad (tabla de años → días).
 * - Días hábiles en un rango (excluyendo sábados y domingos) mediante WeekendExclusionRule.
 *
 * Los días solo se descuentan cuando la solicitud es aprobada (eso se aplica
 * en la capa de aplicación al cambiar estado a "approved").
 */
final class VacationDaysCalculator
{
    /**
     * Tabla días por antigüedad: años completos → días anuales.
     * 1→12, 2→14, 3→16, 4→18, 5→20, 6→22, 7-9→22, 10+→24
     */
    private const DAYS_BY_SENIORITY = [
        1 => 12,
        2 => 14,
        3 => 16,
        4 => 18,
        5 => 20,
        6 => 22,
        7 => 22,
        8 => 22,
        9 => 22,
        10 => 24,
    ];

    public function __construct(
        private readonly WeekendExclusionRule $weekendExclusionRule
    ) {
    }

    /**
     * Días de vacaciones anuales según años de antigüedad (años completos).
     * Menos de 1 año → 0. 10 o más años → 24.
     */
    public function getAnnualDaysBySeniority(int $yearsOfService): int
    {
        if ($yearsOfService < 1) {
            return 0;
        }

        if ($yearsOfService >= 10) {
            return 24;
        }

        return self::DAYS_BY_SENIORITY[$yearsOfService] ?? 22;
    }

    /**
     * Días hábiles (sin sábados ni domingos) en el rango [start, end] inclusive.
     * Es el número de días que consumirá la solicitud una vez aprobada.
     */
    public function getBusinessDaysInRange(DateTimeInterface $start, DateTimeInterface $end): int
    {
        return $this->weekendExclusionRule->countBusinessDays($start, $end);
    }

    /**
     * Años completos de antigüedad desde una fecha de alta hasta una fecha de referencia.
     */
    public static function yearsOfService(DateTimeInterface $hireDate, DateTimeInterface $referenceDate): int
    {
        $hire = \DateTimeImmutable::createFromInterface($hireDate);
        $ref = \DateTimeImmutable::createFromInterface($referenceDate);

        if ($hire > $ref) {
            return 0;
        }

        $diff = $hire->diff($ref);

        return (int) $diff->y;
    }
}
