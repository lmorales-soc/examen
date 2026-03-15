# Flujo de aprobación de solicitudes de vacaciones

## Reglas de negocio

1. **Si un EMPLEADO solicita vacaciones**  
   La solicitud la aprueba o rechaza el **gerente del área** del empleado.

2. **Si un GERENTE solicita vacaciones**  
   (usuario con rol AREA_MANAGER, HR_MANAGER o ADMIN)  
   La solicitud la aprueba **RH** (primer usuario con rol HR_MANAGER o ADMIN).

3. **ADMIN** puede aprobar o rechazar cualquier solicitud.

---

## Componentes (Clean Architecture)

### ApprovalResolverService (`Application/Services`)

Servicio de aplicación que determina **quién** debe aprobar cada solicitud usando solo interfaces de dominio.

| Método | Descripción |
|--------|-------------|
| `getApproverTypeForRequest(array $request)` | Devuelve `'area_manager'` o `'hr'` según el rol del solicitante. |
| `getApproverUserIdForRequest(array $request)` | Devuelve el `user_id` del aprobador (gerente del área o primer HR/ADMIN). `null` si no hay aprobador asignado. |
| `canUserApproveRequest(array $request, int $userId)` | Indica si ese usuario puede aprobar/rechazar (ADMIN siempre; si no, debe ser el aprobador resuelto). |
| `authorizeApproval(array $request, int $userId)` | Valida autorización; lanza `UnauthorizedApprovalException` si el usuario no puede aprobar. |

**Dependencias:** `EmployeeRepositoryInterface`, `AreaRepositoryInterface`, `UserRepositoryInterface`.

### UnauthorizedApprovalException (`Domain/Exceptions`)

Se lanza cuando un usuario intenta aprobar o rechazar una solicitud que no le corresponde según el flujo.

### Contratos de repositorio ampliados

- **AreaRepositoryInterface:** `getAreaManagerUserId(int $areaId): ?int`
- **UserRepositoryInterface:** `getUserRoles(int $userId): array`, `getFirstUserIdWithRole(string $role): ?int`
- **VacationRequestRepositoryInterface:** `create()` acepta opcionalmente `assigned_approver_id` en el array de datos.

---

## Integración con casos de uso

### CreateVacationRequestUseCase

1. Tras validar reglas y antes de persistir, llama a `ApprovalResolverService::getApproverUserIdForRequest()` con el payload de la solicitud (con `employee_id`).
2. Si devuelve un valor, lo guarda en la solicitud como `assigned_approver_id` (para notificaciones y/o consultas).
3. Crea la solicitud con `status = pending` y, si aplica, `assigned_approver_id`.

### ApproveVacationRequestUseCase

1. Carga la solicitud y comprueba que esté en `pending`.
2. Llama a `ApprovalResolverService::authorizeApproval($request, $dto->approvedByUserId)`.
3. Si no lanza, actualiza el estado a `approved` con `approved_by` = usuario que aprueba.

### RejectVacationRequestUseCase

1. Carga la solicitud y comprueba que esté en `pending`.
2. Llama a `ApprovalResolverService::authorizeApproval($request, $dto->rejectedByUserId)`.
3. Si no lanza, actualiza el estado a `rejected` con motivo.

---

## Persistencia

La implementación del repositorio de solicitudes debe:

- Aceptar en `create()` la clave `assigned_approver_id` (nullable) y persistirla si la tabla tiene esa columna.
- Si la tabla `vacation_requests` aún no tiene la columna, añadir migración:

```php
$table->unsignedBigInteger('assigned_approver_id')->nullable()->after('comments');
$table->foreign('assigned_approver_id')->references('id')->on('users')->nullOnDelete();
```

La implementación de **AreaRepository** debe poder devolver el `user_id` del gerente del área (p. ej. columna `manager_user_id` en `areas` o tabla pivot área–usuario).
