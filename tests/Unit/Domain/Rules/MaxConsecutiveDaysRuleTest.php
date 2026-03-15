<?php

namespace Tests\Unit\Domain\Rules;

use App\Domain\Exceptions\TooManyConsecutiveDaysException;
use App\Domain\Rules\MaxConsecutiveDaysRule;
use PHPUnit\Framework\TestCase;

class MaxConsecutiveDaysRuleTest extends TestCase
{
    private MaxConsecutiveDaysRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new MaxConsecutiveDaysRule;
    }

    public function test_count_calendar_days_single_day(): void
    {
        $start = new \DateTimeImmutable('2026-03-16');
        $end = new \DateTimeImmutable('2026-03-16');
        $this->assertSame(1, $this->rule->countCalendarDays($start, $end));
    }

    public function test_count_calendar_days_six_days_allowed(): void
    {
        $start = new \DateTimeImmutable('2026-03-16');
        $end = new \DateTimeImmutable('2026-03-21');
        $this->assertSame(6, $this->rule->countCalendarDays($start, $end));
        $this->assertTrue($this->rule->passes($start, $end));
    }

    public function test_count_calendar_days_seven_days_exceeds_max(): void
    {
        $start = new \DateTimeImmutable('2026-03-16');
        $end = new \DateTimeImmutable('2026-03-22');
        $this->assertSame(7, $this->rule->countCalendarDays($start, $end));
        $this->assertFalse($this->rule->passes($start, $end));
    }

    public function test_validate_throws_when_more_than_six_days(): void
    {
        $start = new \DateTimeImmutable('2026-03-16');
        $end = new \DateTimeImmutable('2026-03-25'); // 10 días

        $this->expectException(TooManyConsecutiveDaysException::class);
        $this->expectExceptionMessage('6 días consecutivos');

        $this->rule->validate($start, $end);
    }

    public function test_validate_passes_when_six_days_or_less(): void
    {
        $start = new \DateTimeImmutable('2026-03-16');
        $end = new \DateTimeImmutable('2026-03-21');

        $this->rule->validate($start, $end);
        $this->assertTrue(true);
    }

    public function test_get_max_consecutive_days_constant(): void
    {
        $this->assertSame(6, MaxConsecutiveDaysRule::getMaxConsecutiveDays());
    }

    public function test_count_calendar_days_start_after_end_returns_zero(): void
    {
        $start = new \DateTimeImmutable('2026-03-22');
        $end = new \DateTimeImmutable('2026-03-16');
        $this->assertSame(0, $this->rule->countCalendarDays($start, $end));
    }
}
