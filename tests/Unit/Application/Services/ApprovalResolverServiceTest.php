<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\ApprovalResolverService;
use App\Domain\Exceptions\UnauthorizedApprovalException;
use App\Domain\Repositories\AreaRepositoryInterface;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ApprovalResolverServiceTest extends TestCase
{
    private EmployeeRepositoryInterface $employeeRepo;

    private AreaRepositoryInterface $areaRepo;

    private UserRepositoryInterface $userRepo;

    private ApprovalResolverService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeRepo = $this->createMock(EmployeeRepositoryInterface::class);
        $this->areaRepo = $this->createMock(AreaRepositoryInterface::class);
        $this->userRepo = $this->createMock(UserRepositoryInterface::class);
        $this->service = new ApprovalResolverService(
            $this->employeeRepo,
            $this->areaRepo,
            $this->userRepo
        );
    }

    public function test_get_approver_type_returns_area_manager_for_employee(): void
    {
        $request = ['employee_id' => 1];
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->with(10)->willReturn(['EMPLOYEE']);

        $this->assertSame('area_manager', $this->service->getApproverTypeForRequest($request));
    }

    public function test_get_approver_type_returns_hr_for_manager_role(): void
    {
        $request = ['employee_id' => 2];
        $this->employeeRepo->method('findById')->with(2)->willReturn([
            'id' => 2, 'user_id' => 20, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->with(20)->willReturn(['AREA_MANAGER']);

        $this->assertSame('hr', $this->service->getApproverTypeForRequest($request));
    }

    public function test_get_approver_user_id_returns_area_manager_for_employee(): void
    {
        $request = ['employee_id' => 1];
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->with(10)->willReturn(['EMPLOYEE']);
        $this->areaRepo->method('getAreaManagerUserId')->with(5)->willReturn(100);

        $this->assertSame(100, $this->service->getApproverUserIdForRequest($request));
    }

    public function test_get_approver_user_id_returns_hr_user_for_manager(): void
    {
        $request = ['employee_id' => 2];
        $this->employeeRepo->method('findById')->with(2)->willReturn([
            'id' => 2, 'user_id' => 20, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->with(20)->willReturn(['AREA_MANAGER']);
        $this->userRepo->method('getFirstUserIdWithRole')->with('HR_MANAGER')->willReturn(50);

        $this->assertSame(50, $this->service->getApproverUserIdForRequest($request));
    }

    public function test_can_user_approve_request_returns_true_for_admin(): void
    {
        $request = ['id' => 1, 'employee_id' => 1];
        $this->userRepo->method('getUserRoles')->with(1)->willReturn(['ADMIN']);

        $this->assertTrue($this->service->canUserApproveRequest($request, 1));
    }

    public function test_can_user_approve_request_returns_true_when_user_is_assigned_approver(): void
    {
        $request = ['employee_id' => 1];
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')
            ->willReturnMap([[100, ['AREA_MANAGER']], [10, ['EMPLOYEE']]]);
        $this->areaRepo->method('getAreaManagerUserId')->with(5)->willReturn(100);

        $this->assertTrue($this->service->canUserApproveRequest($request, 100));
    }

    public function test_can_user_approve_request_returns_false_when_user_is_not_approver(): void
    {
        $request = ['employee_id' => 1];
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->willReturnMap([
            [999, ['EMPLOYEE']],
            [10, ['EMPLOYEE']],
        ]);
        $this->areaRepo->method('getAreaManagerUserId')->with(5)->willReturn(100);

        $this->assertFalse($this->service->canUserApproveRequest($request, 999));
    }

    public function test_authorize_approval_throws_when_user_cannot_approve(): void
    {
        $request = ['id' => 1, 'employee_id' => 1];
        $this->employeeRepo->method('findById')->with(1)->willReturn([
            'id' => 1, 'user_id' => 10, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->willReturnMap([
            [999, ['EMPLOYEE']],
            [10, ['EMPLOYEE']],
        ]);
        $this->areaRepo->method('getAreaManagerUserId')->with(5)->willReturn(100);

        $this->expectException(UnauthorizedApprovalException::class);

        $this->service->authorizeApproval($request, 999);
    }

    public function test_authorize_approval_does_not_throw_when_user_can_approve(): void
    {
        $request = ['id' => 1, 'employee_id' => 1];
        $this->userRepo->method('getUserRoles')->with(1)->willReturn(['ADMIN']);

        $this->service->authorizeApproval($request, 1);
        $this->assertTrue(true);
    }

    /** Las vacaciones de gerentes solo las pueden aprobar ADMIN o HR_MANAGER. */
    public function test_area_manager_cannot_approve_another_managers_vacation_request(): void
    {
        $request = ['id' => 1, 'employee_id' => 2];
        $this->employeeRepo->method('findById')->with(2)->willReturn([
            'id' => 2, 'user_id' => 20, 'area_id' => 5,
        ]);
        $this->userRepo->method('getUserRoles')->willReturnMap([
            [20, ['AREA_MANAGER']],
            [100, ['AREA_MANAGER']],
        ]);
        $this->userRepo->method('getFirstUserIdWithRole')->with('HR_MANAGER')->willReturn(100);

        $this->assertFalse($this->service->canUserApproveRequest($request, 100));
    }
}
