<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\VacationRequestRepositoryInterface;
use App\Models\VacationRequest;

final class EloquentVacationRequestRepository implements VacationRequestRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $request = VacationRequest::with('employee')->find($id);

        return $request ? $this->toArray($request) : null;
    }

    /** @return array<int, array> */
    public function getPending(?int $employeeId = null, ?int $areaId = null, ?int $assignedApproverId = null): array
    {
        $query = VacationRequest::with('employee.area', 'employee.user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($employeeId !== null) {
            $query->where('employee_id', $employeeId);
        }
        if ($areaId !== null) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $areaId));
        }
        if ($assignedApproverId !== null) {
            $query->where('assigned_approver_id', $assignedApproverId);
        }

        $requests = $query->get();
        $result = [];
        foreach ($requests as $r) {
            $result[$r->id] = $this->toArray($r);
        }

        return $result;
    }

    /** @return array<int, array> */
    public function getForListing(?int $employeeId = null, ?int $areaId = null): array
    {
        $query = VacationRequest::with('employee.area', 'employee.user')
            ->orderBy('created_at', 'desc');

        if ($employeeId !== null) {
            $query->where('employee_id', $employeeId);
        }
        if ($areaId !== null) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $areaId));
        }

        $requests = $query->get();
        $result = [];
        foreach ($requests as $r) {
            $result[$r->id] = $this->toArray($r);
        }

        return $result;
    }

    /** @return array<int, array> */
    public function getLatest(int $limit = 5): array
    {
        $requests = VacationRequest::with('employee.area')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($requests as $r) {
            $result[$r->id] = $this->toArray($r);
        }

        return $result;
    }

    public function create(array $data): array
    {
        $request = VacationRequest::create([
            'employee_id' => $data['employee_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days_requested' => $data['days_requested'],
            'status' => $data['status'] ?? 'pending',
            'comments' => $data['comments'] ?? null,
            'assigned_approver_id' => $data['assigned_approver_id'] ?? null,
        ]);

        return $this->toArray($request->load('employee'));
    }

    public function updateStatus(
        int $id,
        string $status,
        ?int $approvedBy = null,
        ?string $rejectionReason = null
    ): array {
        $request = VacationRequest::findOrFail($id);
        $request->update([
            'status' => $status,
            'approved_by' => $approvedBy,
            'rejection_reason' => $rejectionReason,
        ]);

        return $this->toArray($request->fresh(['employee']));
    }

    /** @return array<int, array> */
    public function getForCalendar(int $year, ?int $areaId = null, ?int $employeeId = null, ?array $areaIds = null): array
    {
        $query = VacationRequest::with('employee.area')
            ->where('status', 'approved')
            ->whereYear('start_date', $year);

        if ($employeeId !== null && (empty($areaIds) || $areaIds === null)) {
            $query->where('employee_id', $employeeId);
        } elseif (! empty($areaIds)) {
            if ($employeeId !== null) {
                $query->where(function ($q) use ($employeeId, $areaIds) {
                    $q->where('employee_id', $employeeId)
                        ->orWhereHas('employee', fn ($eq) => $eq->whereIn('area_id', $areaIds));
                });
            } else {
                $query->whereHas('employee', fn ($q) => $q->whereIn('area_id', $areaIds));
            }
        } elseif ($areaId !== null) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $areaId));
        }

        $requests = $query->get();
        $result = [];
        foreach ($requests as $r) {
            $result[$r->id] = $this->toArray($r);
        }

        return $result;
    }

    private function toArray(VacationRequest $request): array
    {
        $arr = [
            'id' => $request->id,
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d'),
            'days_requested' => $request->days_requested,
            'status' => $request->status,
            'approved_by' => $request->approved_by,
            'rejection_reason' => $request->rejection_reason,
            'assigned_approver_id' => $request->assigned_approver_id,
            'comments' => $request->comments,
        ];

        if ($request->relationLoaded('employee')) {
            $arr['employee'] = $request->employee ? [
                'id' => $request->employee->id,
                'first_name' => $request->employee->first_name,
                'last_name' => $request->employee->last_name,
                'area_id' => $request->employee->area_id,
                'area' => $request->employee->relationLoaded('area') && $request->employee->area
                    ? ['id' => $request->employee->area->id, 'name' => $request->employee->area->name]
                    : null,
            ] : null;
        }

        return $arr;
    }
}
