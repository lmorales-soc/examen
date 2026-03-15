<?php

namespace Tests\Unit\Domain\Rules;

use App\Domain\Exceptions\InsufficientVacationDaysException;
use App\Domain\Rules\AvailableDaysRule;
use PHPUnit\Framework\TestCase;

class AvailableDaysRuleTest extends TestCase
{
    private AvailableDaysRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailableDaysRule;
    }

    public function test_passes_when_requested_less_than_available(): void
    {
        $this->assertTrue($this->rule->passes(3, 12));
        $this->assertTrue($this->rule->passes(12, 12));
    }

    public function test_fails_when_requested_more_than_available(): void
    {
        $this->assertFalse($this->rule->passes(5, 3));
        $this->assertFalse($this->rule->passes(13, 12));
    }

    public function test_passes_when_requested_zero(): void
    {
        $this->assertTrue($this->rule->passes(0, 12));
    }

    public function test_validate_does_not_throw_when_requested_within_available(): void
    {
        $this->rule->validate(5, 12);
        $this->rule->validate(12, 12);
        $this->assertTrue(true);
    }

    public function test_validate_does_not_throw_when_requested_zero(): void
    {
        $this->rule->validate(0, 12);
        $this->assertTrue(true);
    }

    public function test_validate_throws_when_requested_exceeds_available(): void
    {
        $this->expectException(InsufficientVacationDaysException::class);
        $this->expectExceptionMessage('5 días');
        $this->expectExceptionMessage('3 disponibles');

        $this->rule->validate(5, 3);
    }
}
