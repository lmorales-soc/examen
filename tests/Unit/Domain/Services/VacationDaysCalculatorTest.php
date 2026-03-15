<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Rules\WeekendExclusionRule;
use App\Domain\Services\VacationDaysCalculator;
use PHPUnit\Framework\TestCase;

class VacationDaysCalculatorTest extends TestCase
{
    private VacationDaysCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new VacationDaysCalculator(new WeekendExclusionRule);
    }

    public function test_get_annual_days_by_seniority_less_than_one_year(): void
    {
        $this->assertSame(0, $this->calculator->getAnnualDaysBySeniority(0));
    }

    public function test_get_annual_days_by_seniority_one_year(): void
    {
        $this->assertSame(12, $this->calculator->getAnnualDaysBySeniority(1));
    }

    public function test_get_annual_days_by_seniority_two_years(): void
    {
        $this->assertSame(14, $this->calculator->getAnnualDaysBySeniority(2));
    }

    public function test_get_annual_days_by_seniority_five_years(): void
    {
        $this->assertSame(20, $this->calculator->getAnnualDaysBySeniority(5));
    }

    public function test_get_annual_days_by_seniority_six_years(): void
    {
        $this->assertSame(22, $this->calculator->getAnnualDaysBySeniority(6));
    }

    public function test_get_annual_days_by_seniority_ten_or_more(): void
    {
        $this->assertSame(24, $this->calculator->getAnnualDaysBySeniority(10));
        $this->assertSame(24, $this->calculator->getAnnualDaysBySeniority(15));
    }

    public function test_years_of_service_same_day(): void
    {
        $hire = new \DateTimeImmutable('2024-01-15');
        $ref = new \DateTimeImmutable('2024-01-15');
        $this->assertSame(0, VacationDaysCalculator::yearsOfService($hire, $ref));
    }

    public function test_years_of_service_one_year(): void
    {
        $hire = new \DateTimeImmutable('2024-01-15');
        $ref = new \DateTimeImmutable('2025-01-15');
        $this->assertSame(1, VacationDaysCalculator::yearsOfService($hire, $ref));
    }

    public function test_years_of_service_hire_after_reference(): void
    {
        $hire = new \DateTimeImmutable('2026-01-15');
        $ref = new \DateTimeImmutable('2025-01-15');
        $this->assertSame(0, VacationDaysCalculator::yearsOfService($hire, $ref));
    }

    public function test_get_business_days_in_range_delegates_to_weekend_rule(): void
    {
        $start = new \DateTimeImmutable('2026-03-16'); // lunes
        $end = new \DateTimeImmutable('2026-03-20');   // viernes
        $this->assertSame(5, $this->calculator->getBusinessDaysInRange($start, $end));
    }
}
