<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateEmployeeDTO;
use App\Application\DTOs\DeactivateEmployeeDTO;
use App\Application\DTOs\UpdateEmployeeDTO;
use App\Application\UseCases\Area\ListAreasUseCase;
use App\Application\UseCases\Employee\CreateEmployeeUseCase;
use App\Application\UseCases\Employee\DeactivateEmployeeUseCase;
use App\Application\UseCases\Employee\GetEmployeeUseCase;
use App\Application\UseCases\Employee\ListEmployeesUseCase;
use App\Application\UseCases\Employee\UpdateEmployeeUseCase;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly ListEmployeesUseCase $listEmployeesUseCase,
        private readonly ListAreasUseCase $listAreasUseCase,
        private readonly GetEmployeeUseCase $getEmployeeUseCase,
        private readonly CreateEmployeeUseCase $createEmployeeUseCase,
        private readonly UpdateEmployeeUseCase $updateEmployeeUseCase,
        private readonly DeactivateEmployeeUseCase $deactivateEmployeeUseCase,
    ) {
    }

    public function index(Request $request): View
    {
        $areaId = $request->query('area_id') ? (int) $request->query('area_id') : null;
        $activeOnly = $request->boolean('active_only', true);
        $employees = $this->listEmployeesUseCase->execute($activeOnly, $areaId);

        return view('employees.index', [
            'employees' => $employees,
            'areaId' => $areaId,
        ]);
    }

    public function create(): View
    {
        $areas = $this->listAreasUseCase->execute(false);

        return view('employees.create', ['areas' => $areas]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $dto = new CreateEmployeeDTO(
            userName: $v['name'],
            userEmail: $v['email'],
            userPassword: $v['password'],
            role: $v['role'],
            areaId: (int) $v['area_id'],
            employeeNumber: $v['employee_number'],
            firstName: $v['first_name'],
            lastName: $v['last_name'],
            hireDate: new \DateTimeImmutable($v['hire_date']),
            vacationDaysAnnual: isset($v['vacation_days_annual']) ? (int) $v['vacation_days_annual'] : null,
        );

        $employee = $this->createEmployeeUseCase->execute($dto);

        return redirect()
            ->route('employees.show', $employee['id'])
            ->with('success', 'Empleado y acceso al sistema creados correctamente. El empleado ya puede iniciar sesión con su correo y contraseña.');
    }

    public function show(int $employee): View|RedirectResponse
    {
        $employeeData = $this->getEmployeeUseCase->execute($employee);
        if (! $employeeData) {
            return redirect()->route('employees.index')->with('error', 'Empleado no encontrado.');
        }

        return view('employees.show', ['employee' => $employeeData]);
    }

    public function edit(int $employee): View|RedirectResponse
    {
        $employeeData = $this->getEmployeeUseCase->execute($employee);
        if (! $employeeData) {
            return redirect()->route('employees.index')->with('error', 'Empleado no encontrado.');
        }

        return view('employees.edit', ['employee' => $employeeData]);
    }

    public function update(UpdateEmployeeRequest $request, int $employee): RedirectResponse
    {
        $validated = $request->validated();
        $dto = new UpdateEmployeeDTO(
            id: $employee,
            areaId: isset($validated['area_id']) ? (int) $validated['area_id'] : null,
            employeeNumber: $validated['employee_number'] ?? null,
            firstName: $validated['first_name'] ?? null,
            lastName: $validated['last_name'] ?? null,
            hireDate: isset($validated['hire_date'])
                ? new \DateTimeImmutable($validated['hire_date'])
                : null,
            vacationDaysAnnual: array_key_exists('vacation_days_annual', $validated)
                ? (int) $validated['vacation_days_annual']
                : null,
        );

        $this->updateEmployeeUseCase->execute($dto);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(int $employee): RedirectResponse
    {
        $dto = new DeactivateEmployeeDTO(employeeId: $employee);
        $this->deactivateEmployeeUseCase->execute($dto);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado desactivado correctamente.');
    }
}
