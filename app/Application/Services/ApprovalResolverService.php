<?php

namespace App\Application\Services;

use App\Domain\Repositories\AreaRepositoryInterface;
use App\Domain\Repositories\EmployeeRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

/**
 * Determina quién debe aprobar cada solicitud de vacaciones según reglas de negocio:
 *
 * - Si un EMPLEADO solicita → aprueba el GERENTE DEL ÁREA del empleado.
 * - Si un GERENTE (AREA_MANAGER, HR_MANAGER, ADMIN) solicita → aprueba RH.
 *
 * Capa Application: orquesta repositorios para resolver el aprobador.
 */
final class ApprovalResolverService
{
    private const APPROVER_AREA_MANAGER = 'area_manager';
    private const APPROVER_HR = 'hr';

    /** Roles que requieren aprobación por RH (no por gerente de área). */
    private const MANAGER_ROLES = ['AREA_MANAGER', 'HR_MANAGER', 'ADMIN'];

    /** Rol prioritario para asignar aprobador RH cuando el solicitante es gerente. */
    private const HR_APPROVER_ROLES = ['HR_MANAGER', 'ADMIN'];

    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly AreaRepositoryInterface $areaRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * Tipo de aprobador para la solicitud: 'area_manager' o 'hr'.
     *
     * @param array{employee_id: int} $request Solicitud con al menos employee_id
     */
    public function getApproverTypeForRequest(array $request): string
    {
        $employee = $this->employeeRepository->findById((int) $request['employee_id']);
        if (! $employee || empty($employee['user_id'])) {
            return self::APPROVER_HR; // fallback seguro
        }

        $requesterRoles = $this->userRepository->getUserRoles((int) $employee['user_id']);
        $isManager = ! empty(array_intersect(self::MANAGER_ROLES, $requesterRoles));

        return $isManager ? self::APPROVER_HR : self::APPROVER_AREA_MANAGER;
    }

    /**
     * ID del usuario que debe aprobar esta solicitud (gerente del área o primer HR/ADMIN).
     * Null si no se puede determinar (ej. área sin gerente asignado).
     *
     * @param array{employee_id: int} $request Solicitud con al menos employee_id
     */
    public function getApproverUserIdForRequest(array $request): ?int
    {
        $type = $this->getApproverTypeForRequest($request);

        if ($type === self::APPROVER_AREA_MANAGER) {
            $employee = $this->employeeRepository->findById((int) $request['employee_id']);
            if (! $employee || empty($employee['area_id'])) {
                return null;
            }
            return $this->areaRepository->getAreaManagerUserId((int) $employee['area_id']);
        }

        foreach (self::HR_APPROVER_ROLES as $role) {
            $userId = $this->userRepository->getFirstUserIdWithRole($role);
            if ($userId !== null) {
                return $userId;
            }
        }

        return null;
    }

    /**
     * Indica si el usuario dado puede aprobar o rechazar la solicitud.
     * ADMIN puede siempre; en resto se exige ser el aprobador resuelto.
     * Las vacaciones de gerentes solo pueden autorizarlas ADMIN o HR_MANAGER.
     *
     * @param array $request Solicitud completa (incluye employee_id)
     */
    public function canUserApproveRequest(array $request, int $userId): bool
    {
        $userRoles = $this->userRepository->getUserRoles($userId);
        $userRolesUpper = array_map('strtoupper', array_map('trim', $userRoles));
        if (in_array('ADMIN', $userRolesUpper, true)) {
            return true;
        }

        $approverUserId = $this->getApproverUserIdForRequest($request);
        if ($approverUserId === null) {
            return false;
        }

        $employee = $this->employeeRepository->findById((int) ($request['employee_id'] ?? 0));
        if ($employee && ! empty($employee['user_id'])) {
            $requesterRoles = $this->userRepository->getUserRoles((int) $employee['user_id']);
            $requesterUpper = array_map('strtoupper', array_map('trim', $requesterRoles));
            $requesterIsManager = ! empty(array_intersect(self::MANAGER_ROLES, $requesterUpper));
            if ($requesterIsManager && ! in_array('HR_MANAGER', $userRolesUpper, true)) {
                return false;
            }
        }

        return $approverUserId === $userId;
    }

    /**
     * Ejecuta la validación de autorización: lanza si el usuario no puede aprobar.
     *
     * @param array $request Solicitud completa
     * @throws \App\Domain\Exceptions\UnauthorizedApprovalException
     */
    public function authorizeApproval(array $request, int $userId): void
    {
        if (! $this->canUserApproveRequest($request, $userId)) {
            throw new \App\Domain\Exceptions\UnauthorizedApprovalException(
                (int) $request['id'],
                $userId
            );
        }
    }
}
