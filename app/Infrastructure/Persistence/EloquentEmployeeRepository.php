<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\VacationRequest;

final class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $employee = Employee::find($id);

        return $employee ? $this->toArray($employee) : null;
    }

    public function findByUserId(int $userId): ?array
    {
        $employee = Employee::where('user_id', $userId)->first();

        return $employee ? $this->toArray($employee) : null;
    }

    public function findByEmployeeNumber(string $employeeNumber): ?array
    {
        $employee = Employee::where('employee_number', $employeeNumber)->first();

        return $employee ? $this->toArray($employee) : null;
    }

    /** @return array<int, array> */
    public function list(bool $activeOnly = true, ?int $areaId = null): array
    {
        $query = Employee::with('user', 'area')->orderBy('last_name')->orderBy('first_name');
        if ($activeOnly) {
            $query->where('active', true);
        }
        if ($areaId !== null) {
            $query->where('area_id', $areaId);
        }
        $employees = $query->get();
        $result = [];
        foreach ($employees as $e) {
            $result[$e->id] = $this->toArray($e);
        }

        return $result;
    }

    public function create(array $data): array
    {
        $employee = Employee::create([
            'user_id' => $data['user_id'],
            'area_id' => $data['area_id'],
            'employee_number' => $data['employee_number'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'hire_date' => $data['hire_date'],
            'vacation_days_annual' => $data['vacation_days_annual'] ?? 12,
            'active' => true,
        ]);

        return $this->toArray($employee);
    }

    public function update(int $id, array $data): array
    {
        $employee = Employee::findOrFail($id);
        $employee->update(array_filter($data, fn ($v) => $v !== null));

        return $this->toArray($employee->fresh());
    }

    public function deactivate(int $id): void
    {
        Employee::where('id', $id)->update(['active' => false]);
    }

    public function getAvailableVacationDays(int $employeeId, int $year): int
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return 0;
        }

        $annual = (int) $employee->vacation_days_annual;
        $consumed = (int) VacationRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_requested');

        return max(0, $annual - $consumed);
    }

    private function toArray(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'area_id' => $employee->area_id,
            'employee_number' => $employee->employee_number,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'hire_date' => $employee->hire_date?->format('Y-m-d'),
            'vacation_days_annual' => $employee->vacation_days_annual,
            'active' => $employee->active,
        ];
    }
}
