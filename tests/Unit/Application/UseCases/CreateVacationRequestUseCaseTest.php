<?php

namespace Tests\Unit\Application\UseCases;

use App\Application\DTOs\CreateVacationRequestDTO;
use App\Application\Services\ApprovalResolverService;
use App\Application\UseCases\VacationRequest\CreateVacationRequestUseCase;
use App\Domain\Exceptions\InsufficientVacationDaysException;
use App\Domain\Exceptions\TooManyConsecutiveDaysException;
use App\Domain\Repositories\AreaRepositoryInterface;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\VacationRequestRepositoryInterface;
use App\Domain\Rules\AvailableDaysRule;
use App\Domain\Rules\MaxConsecutiveDaysRule;
use App\Domain\Rules\WeekendExclusionRule;
use App\Domain\Services\VacationDaysCalculator;
use PHPUnit\Framework\TestCase;

class CreateVacationRequestUseCaseTest extends TestCase
{
    private VacationRequestRepositoryInterface $vacationRequestRepo;

    private EmployeeRepositoryInterface $employeeRepo;

    private AreaRepositoryInterface $areaRepo;

    private UserRepositoryInterface $userRepo;

    private ApprovalResolverService $approvalResolver;

    private CreateVacationRequestUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vacationRequestRepo = $this->createMock(VacationRequestRepositoryInterface::class);
        $this->employeeRepo = $this->createMock(EmployeeRepositoryInterface::class);
        $this->areaRepo = $this->createMock(AreaRepositoryInterface::class);
        $this->userRepo = $this->createMock(UserRepositoryInterface::class);
        $this->approvalResolver = new ApprovalResolverService(
            $this->employeeRepo,
            $this->areaRepo,
            $this->userRepo
        );

        $weekendRule = new WeekendExclusionRule;
        $calculator = new VacationDaysCalculator($weekendRule);

        $this->useCase = new CreateVacationRequestUseCase(
            $this->vacationRequestRepo,
            $this->employeeRepo,
            $calculator,
            new MaxConsecutiveDaysRule,
            new AvailableDaysRule,
            $this->approvalResolver
        );
    }

    public function test_execute_creates_request_when_rules_pass(): void
    {
        $dto = new CreateVacationRequestDTO(
            employeeId: 1,
            startDate: new \DateTimeImmutable('2026-03-16'), // lunes
            endDate: new \DateTimeImmutable('2026-03-18'),     // miércoles = 3 días hábiles
            comments: null
        );

        $this->employeeRepo->method('getAvailableVacationDays')->with(1, 2026)->willReturn(12);
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5, 'first_name' => 'Juan', 'last_name' => 'Pérez',
        ]);
        $this->userRepo->method('getUserRoles')->with(10)->willReturn(['EMPLOYEE']);
        $this->areaRepo->method('getAreaManagerUserId')->with(5)->willReturn(100);

        $created = ['id' => 99, 'employee_id' => 1, 'status' => 'pending', 'days_requested' => 3];
        $this->vacationRequestRepo->expects($this->once())->method('create')->with($this->callback(function (array $payload) {
            return $payload['employee_id'] === 1
                && $payload['days_requested'] === 3
                && $payload['status'] === 'pending'
                && isset($payload['assigned_approver_id']) && $payload['assigned_approver_id'] === 100;
        }))->willReturn($created);

        $result = $this->useCase->execute($dto);

        $this->assertSame(99, $result['id']);
        $this->assertSame('pending', $result['status']);
    }

    public function test_execute_throws_when_more_than_six_consecutive_days(): void
    {
        $dto = new CreateVacationRequestDTO(
            employeeId: 1,
            startDate: new \DateTimeImmutable('2026-03-16'),
            endDate: new \DateTimeImmutable('2026-03-25'), // 10 días
            comments: null
        );

        $this->expectException(TooManyConsecutiveDaysException::class);

        $this->useCase->execute($dto);
    }

    public function test_execute_throws_when_insufficient_vacation_days(): void
    {
        $dto = new CreateVacationRequestDTO(
            employeeId: 1,
            startDate: new \DateTimeImmutable('2026-03-16'),
            endDate: new \DateTimeImmutable('2026-03-20'), // 5 días hábiles
            comments: null
        );

        $this->employeeRepo->method('getAvailableVacationDays')->with(1, 2026)->willReturn(2);

        $this->expectException(InsufficientVacationDaysException::class);

        $this->useCase->execute($dto);
    }

    public function test_execute_throws_when_no_business_days_in_range(): void
    {
        $dto = new CreateVacationRequestDTO(
            employeeId: 1,
            startDate: new \DateTimeImmutable('2026-03-21'), // sábado
            endDate: new \DateTimeImmutable('2026-03-22'),   // domingo
            comments: null
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('días hábiles');

        $this->useCase->execute($dto);
    }

    public function test_execute_throws_when_employee_not_found(): void
    {
        $dto = new CreateVacationRequestDTO(
            employeeId: 999,
            startDate: new \DateTimeImmutable('2026-03-16'),
            endDate: new \DateTimeImmutable('2026-03-18'),
            comments: null
        );

        $this->employeeRepo->method('getAvailableVacationDays')->with(999, 2026)->willReturn(12);
        $this->employeeRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empleado no encontrado');

        $this->useCase->execute($dto);
    }
}
