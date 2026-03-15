<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateVacationRequestDTO;
use App\Application\DTOs\GetVacationCalendarDTO;
use App\Application\UseCases\VacationRequest\CreateVacationRequestUseCase;
use App\Application\UseCases\VacationRequest\GetVacationCalendarUseCase;
use App\Application\UseCases\VacationRequest\GetVacationRequestsListingUseCase;
use App\Domain\Exceptions\InsufficientVacationDaysException;
use App\Domain\Exceptions\TooManyConsecutiveDaysException;
use App\Domain\Repositories\AreaRepositoryInterface;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Http\Requests\StoreVacationRequestRequest;
use App\Models\Area;
use App\Models\User;
use App\Notifications\VacationRequestCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VacationRequestController extends Controller
{
    /** Paleta de colores por empleado (mismo índice = mismo color por employee_id). */
    private const CALENDAR_COLORS = [
        '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e', '#16a085', '#c0392b',
    ];

    public function __construct(
        private readonly CreateVacationRequestUseCase $createVacationRequestUseCase,
        private readonly GetVacationRequestsListingUseCase $getVacationRequestsListingUseCase,
        private readonly GetVacationCalendarUseCase $getVacationCalendarUseCase,
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly AreaRepositoryInterface $areaRepository,
    ) {
    }

    /**
     * Listado de solicitudes: empleados ven solo las suyas; RH y ADMIN ven todas.
     */
    public function index(Request $request): View
    {
        $employeeId = null;
        $areaId = $request->filled('area_id') ? (int) $request->query('area_id') : null;
        if ($request->filled('employee_id')) {
            $employeeId = (int) $request->query('employee_id');
        } elseif (Auth::check()) {
            $employee = $this->employeeRepository->findByUserId((int) Auth::id());
            if ($employee && ! Auth::user()->hasAnyRole(['HR_MANAGER', 'ADMIN'])) {
                $employeeId = (int) $employee['id'];
            }
        }
        $requests = $this->getVacationRequestsListingUseCase->execute($employeeId, $areaId);

        return view('vacation-requests.index', ['requests' => $requests]);
    }

    public function create(): View|RedirectResponse
    {
        $employee = Auth::check()
            ? $this->employeeRepository->findByUserId((int) Auth::id())
            : null;

        if (! $employee) {
            return redirect()
                ->route('vacation-requests.index')
                ->with('error', 'Debe tener un registro de empleado vinculado a su usuario para solicitar vacaciones.');
        }

        return view('vacation-requests.create', ['employee' => $employee]);
    }

    public function store(StoreVacationRequestRequest $request): RedirectResponse
    {
        try {
            $dto = new CreateVacationRequestDTO(
                employeeId: (int) $request->validated('employee_id'),
                startDate: new \DateTimeImmutable($request->validated('start_date')),
                endDate: new \DateTimeImmutable($request->validated('end_date')),
                comments: $request->validated('comments'),
            );

            $vacationRequest = $this->createVacationRequestUseCase->execute($dto);

            $this->notifyApproverIfAssigned($vacationRequest);

            return redirect()
                ->route('vacation-requests.index')
                ->with('success', 'Solicitud de vacaciones creada correctamente.');
        } catch (TooManyConsecutiveDaysException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (InsufficientVacationDaysException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Notifica al aprobador asignado (email + notificación interna).
     */
    private function notifyApproverIfAssigned(array $vacationRequest): void
    {
        $approverId = $vacationRequest['assigned_approver_id'] ?? null;
        if ($approverId === null) {
            return;
        }

        $approver = User::find($approverId);
        if (! $approver) {
            return;
        }

        $employee = $this->employeeRepository->findById((int) ($vacationRequest['employee_id'] ?? 0));
        $employeeName = $employee
            ? trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))
            : 'Empleado #' . ($vacationRequest['employee_id'] ?? '');
        if ($employeeName === '') {
            $employeeName = 'Empleado #' . ($vacationRequest['employee_id'] ?? '');
        }

        $payload = array_merge($vacationRequest, ['employee_name' => $employeeName]);
        $approver->notify(new VacationRequestCreatedNotification($payload));
    }

    /**
     * Calendario: empleados solo sus vacaciones; gerentes las suyas y de su área; Admin/RH todos.
     */
    public function calendar(Request $request): View
    {
        $year = (int) ($request->query('year') ?? date('Y'));
        $areaId = $request->filled('area_id') ? (int) $request->query('area_id') : null;
        $dto = $this->buildCalendarDTOForCurrentUser($year, $areaId);
        $events = $this->getVacationCalendarUseCase->execute($dto);

        $legend = $this->buildCalendarLegend($events);

        return view('vacation-requests.calendar', [
            'year' => $year,
            'events' => $events,
            'legend' => $legend,
        ]);
    }

    /**
     * Construye el DTO del calendario según el rol: empleado (solo suyas), gerente (suyas + su área), Admin/RH (todos).
     */
    private function buildCalendarDTOForCurrentUser(int $year, ?int $areaId = null): GetVacationCalendarDTO
    {
        $user = Auth::user();
        $employeeId = null;
        $areaIds = null;

        if ($user->hasAnyRole(['ADMIN', 'HR_MANAGER'])) {
            return new GetVacationCalendarDTO(year: $year, areaId: $areaId);
        }

        if ($user->hasRole('AREA_MANAGER')) {
            $areaIds = Area::where('manager_user_id', $user->id)->pluck('id')->toArray();
            $employee = $this->employeeRepository->findByUserId((int) $user->id);
            $employeeId = $employee ? (int) $employee['id'] : null;
            if (empty($areaIds) && $employeeId === null) {
                $areaIds = [0];
            }
            return new GetVacationCalendarDTO(year: $year, areaId: $areaId, employeeId: $employeeId, areaIds: $areaIds ?: [0]);
        }

        $employee = $this->employeeRepository->findByUserId((int) $user->id);
        if ($employee) {
            $employeeId = (int) $employee['id'];
        } else {
            $employeeId = 0;
        }

        return new GetVacationCalendarDTO(year: $year, areaId: $areaId, employeeId: $employeeId);
    }

    /**
     * Construye la leyenda de empleados con vacaciones (nombre + color único por employee_id).
     *
     * @param  array<int, array>  $events
     * @return array<int, array{employee_id: int, name: string, color: string}>
     */
    private function buildCalendarLegend(array $events): array
    {
        $byEmployee = [];
        foreach ($events as $r) {
            $eid = (int) ($r['employee_id'] ?? 0);
            if ($eid === 0) {
                continue;
            }
            if (isset($byEmployee[$eid])) {
                continue;
            }
            $name = 'Empleado #' . $eid;
            if (! empty($r['employee']['first_name']) || ! empty($r['employee']['last_name'])) {
                $name = trim(($r['employee']['first_name'] ?? '') . ' ' . ($r['employee']['last_name'] ?? ''));
            }
            $byEmployee[$eid] = [
                'employee_id' => $eid,
                'name' => $name,
                'color' => self::CALENDAR_COLORS[$eid % count(self::CALENDAR_COLORS)],
            ];
        }

        return array_values($byEmployee);
    }

    private static function colorForEmployeeId(int $employeeId): string
    {
        return self::CALENDAR_COLORS[$employeeId % count(self::CALENDAR_COLORS)];
    }

    /**
     * Endpoint JSON para FullCalendar. Mismos filtros por rol: empleado solo suyas, gerente suyas + área, Admin/RH todos.
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $areaId = $request->filled('area_id') ? (int) $request->query('area_id') : null;

        $year = $start ? (int) date('Y', strtotime($start)) : (int) date('Y');
        $dto = $this->buildCalendarDTOForCurrentUser($year, $areaId);
        $requests = $this->getVacationCalendarUseCase->execute($dto);

        $events = [];
        foreach ($requests as $r) {
            if (($r['status'] ?? '') !== 'approved') {
                continue;
            }
            $employee = $this->employeeRepository->findById((int) ($r['employee_id'] ?? 0));
            $areaName = '—';
            if ($employee && ! empty($employee['area_id'])) {
                $area = $this->areaRepository->findById((int) $employee['area_id']);
                $areaName = $area['name'] ?? '—';
            }
            $employeeName = $employee
                ? trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))
                : 'Empleado #' . ($r['employee_id'] ?? '');
            if ($employeeName === '') {
                $employeeName = 'Empleado #' . ($r['employee_id'] ?? '');
            }

            $startDate = $r['start_date'] ?? null;
            $endDate = $r['end_date'] ?? null;
            if (! $startDate || ! $endDate) {
                continue;
            }
            $endDateExclusive = date('Y-m-d', strtotime($endDate . ' +1 day'));

            $employeeId = (int) ($r['employee_id'] ?? 0);
            $color = self::colorForEmployeeId($employeeId);

            $events[] = [
                'id' => $r['id'] ?? uniqid('', true),
                'title' => $employeeName . ' · ' . $areaName,
                'start' => $startDate,
                'end' => $endDateExclusive,
                'allDay' => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'employeeName' => $employeeName,
                    'area' => $areaName,
                    'daysRequested' => (int) ($r['days_requested'] ?? 0),
                    'employeeId' => $employeeId,
                ],
            ];
        }

        return response()->json($events);
    }
}
