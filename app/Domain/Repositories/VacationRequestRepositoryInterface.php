<?php

namespace App\Domain\Repositories;

interface VacationRequestRepositoryInterface
{
    public function findById(int $id): ?array;

    /** @return array<int, array> */
    public function getPending(?int $employeeId = null, ?int $areaId = null, ?int $assignedApproverId = null): array;

    /** @return array<int, array> Todas las solicitudes (cualquier estado) para listado/historial */
    public function getForListing(?int $employeeId = null, ?int $areaId = null): array;

    /** @return array<int, array> Últimas N solicitudes (cualquier estado), ordenadas por created_at desc */
    public function getLatest(int $limit = 5): array;

    /** $data puede incluir assigned_approver_id (int|null) para flujo de aprobación. */
    public function create(array $data): array;

    public function updateStatus(int $id, string $status, ?int $approvedBy = null, ?string $rejectionReason = null): array;

    /**
     * Fechas de vacaciones aprobadas para calendario.
     * Si employeeId: solo ese empleado. Si areaIds: empleados de esas áreas. Si ambos: empleado o áreas.
     *
     * @param  array<int>|null  $areaIds
     * @return array<int, array>
     */
    public function getForCalendar(int $year, ?int $areaId = null, ?int $employeeId = null, ?array $areaIds = null): array;
}
