<?php

namespace App\Http\Controllers;

use App\Application\DTOs\ApproveVacationRequestDTO;
use App\Application\DTOs\GetPendingRequestsDTO;
use App\Application\DTOs\RejectVacationRequestDTO;
use App\Application\UseCases\VacationRequest\ApproveVacationRequestUseCase;
use App\Application\UseCases\VacationRequest\GetPendingRequestsUseCase;
use App\Application\UseCases\VacationRequest\RejectVacationRequestUseCase;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Http\Requests\ApproveVacationRequestRequest;
use App\Http\Requests\RejectVacationRequestRequest;
use App\Models\User;
use App\Notifications\VacationRequestApprovedNotification;
use App\Notifications\VacationRequestRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly GetPendingRequestsUseCase $getPendingRequestsUseCase,
        private readonly ApproveVacationRequestUseCase $approveVacationRequestUseCase,
        private readonly RejectVacationRequestUseCase $rejectVacationRequestUseCase,
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    /**
     * Listado de solicitudes pendientes asignadas al usuario actual (solo las que puede aprobar).
     * Gerente de área: solo su área. RH: solo solicitudes de gerentes. ADMIN: todas las asignadas a él.
     */
    public function index(Request $request): View
    {
        $dto = new GetPendingRequestsDTO(
            employeeId: null,
            areaId: $request->filled('area_id') ? (int) $request->query('area_id') : null,
            assignedApproverId: (int) Auth::id(),
        );
        $requests = $this->getPendingRequestsUseCase->execute($dto);

        return view('approvals.index', ['requests' => $requests]);
    }

    public function approve(ApproveVacationRequestRequest $request): RedirectResponse
    {
        $userId = (int) Auth::id();
        $dto = new ApproveVacationRequestDTO(
            requestId: (int) $request->validated('request_id'),
            approvedByUserId: $userId,
        );

        $updatedRequest = $this->approveVacationRequestUseCase->execute($dto);
        $this->notifyEmployeeIfExists($updatedRequest, VacationRequestApprovedNotification::class);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Solicitud aprobada correctamente.');
    }

    public function reject(RejectVacationRequestRequest $request): RedirectResponse
    {
        $userId = (int) Auth::id();
        $dto = new RejectVacationRequestDTO(
            requestId: (int) $request->validated('request_id'),
            rejectedByUserId: $userId,
            rejectionReason: $request->validated('rejection_reason'),
        );

        $updatedRequest = $this->rejectVacationRequestUseCase->execute($dto);
        $this->notifyEmployeeIfExists($updatedRequest, VacationRequestRejectedNotification::class);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Solicitud rechazada.');
    }

    private function notifyEmployeeIfExists(array $vacationRequest, string $notificationClass, ?array $payload = null): void
    {
        $employeeId = (int) ($vacationRequest['employee_id'] ?? 0);
        if ($employeeId === 0) {
            return;
        }
        $employee = $this->employeeRepository->findById($employeeId);
        if (! $employee || empty($employee['user_id'])) {
            return;
        }
        $user = User::find((int) $employee['user_id']);
        if ($user) {
            $user->notify(new $notificationClass($payload ?? $vacationRequest));
        }
    }
}
