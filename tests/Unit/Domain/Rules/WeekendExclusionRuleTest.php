<?php

namespace Tests\Unit\Domain\Rules;

use App\Domain\Rules\WeekendExclusionRule;
use PHPUnit\Framework\TestCase;

class WeekendExclusionRuleTest extends TestCase
{
    private WeekendExclusionRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new WeekendExclusionRule;
    }

    public function test_count_business_days_single_weekday(): void
    {
        $start = new \DateTimeImmutable('2026-03-16'); // lunes
        $end = new \DateTimeImmutable('2026-03-16');
        $this->assertSame(1, $this->rule->countBusinessDays($start, $end));
    }

    public function test_count_business_days_single_weekend_day(): void
    {
        $start = new \DateTimeImmutable('2026-03-21'); // sábado
        $end = new \DateTimeImmutable('2026-03-21');
        $this->assertSame(0, $this->rule->countBusinessDays($start, $end));
    }

    public function test_count_business_days_monday_to_friday_one_week(): void
    {
        $start = new \DateTimeImmutable('2026-03-16'); // lunes
        $end = new \DateTimeImmutable('2026-03-20');   // viernes
        $this->assertSame(5, $this->rule->countBusinessDays($start, $end));
    }

    public function test_count_business_days_week_including_weekend(): void
    {
        $start = new \DateTimeImmutable('2026-03-16'); // lunes
        $end = new \DateTimeImmutable('2026-03-22');   // domingo
        $this->assertSame(5, $this->rule->countBusinessDays($start, $end));
    }

    public function test_count_business_days_two_weeks(): void
    {
        $start = new \DateTimeImmutable('2026-03-16'); // lunes
        $end = new \DateTimeImmutable('2026-03-27');   // viernes siguiente semana
        $this->assertSame(10, $this->rule->countBusinessDays($start, $end));
    }

    public function test_count_business_days_start_after_end_returns_zero(): void
    {
        $start = new \DateTimeImmutable('2026-03-20');
        $end = new \DateTimeImmutable('2026-03-16');
        $this->assertSame(0, $this->rule->countBusinessDays($start, $end));
    }

    public function test_is_weekend_saturday(): void
    {
        $this->assertTrue($this->rule->isWeekend(new \DateTimeImmutable('2026-03-21')));
    }

    public function test_is_weekend_sunday(): void
    {
        $this->assertTrue($this->rule->isWeekend(new \DateTimeImmutable('2026-03-22')));
    }

    public function test_is_weekend_monday(): void
    {
        $this->assertFalse($this->rule->isWeekend(new \DateTimeImmutable('2026-03-16')));
    }
}
