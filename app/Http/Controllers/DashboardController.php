<?php

namespace App\Http\Controllers;

use App\Application\DTOs\GetPendingRequestsDTO;
use App\Application\UseCases\VacationRequest\GetPendingRequestsUseCase;
use App\Domain\Repositories\VacationRequestRepositoryInterface;
use App\Models\Employee;
use App\Models\VacationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GetPendingRequestsUseCase $getPendingRequestsUseCase,
        private readonly VacationRequestRepositoryInterface $vacationRequestRepository,
    ) {
    }

    /**
     * Solo ADMIN y HR_MANAGER pueden ver el dashboard.
     * Empleados y gerentes de área son redirigidos a solicitudes o aprobaciones.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['ADMIN', 'HR_MANAGER'])) {
            if ($user->hasRole('AREA_MANAGER')) {
                return redirect()->route('approvals.index');
            }
            return redirect()->route('vacation-requests.index');
        }

        $dto = new GetPendingRequestsDTO(employeeId: null, areaId: null);
        $pendingRequests = $this->getPendingRequestsUseCase->execute($dto);
        $latestRequests = VacationRequest::with('employee.area')
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        $totalRequests = VacationRequest::count();
        $totalEmployees = Employee::where('active', true)->count();

        return view('dashboard.index', [
            'pendingRequests' => $pendingRequests,
            'latestRequests' => $latestRequests,
            'totalRequests' => $totalRequests,
            'totalEmployees' => $totalEmployees,
        ]);
    }
}
