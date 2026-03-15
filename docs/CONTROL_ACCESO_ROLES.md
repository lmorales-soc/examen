# Control de acceso por roles

El sistema aplica **middleware de roles** (Spatie) y filtros en controladores para cumplir con lo que cada usuario puede ver y hacer.

## 4. Roles del sistema

### 4.1 Administrador del sistema (ADMIN)

- **Responsabilidades:** Crear al primer Gerente de RH, administrar configuración básica, asignar roles.
- **Restricción:** Solo puede existir **1 Gerente de Recursos Humanos** activo (validado al asignar rol `HR_MANAGER`).
- **Rutas protegidas:** `role:ADMIN`
  - `/admin-only`
  - `POST /assign-role`, `POST /add-role`
- **Navbar:** Ve Dashboard, Solicitudes, Aprobaciones (si tiene rol), Notificaciones.

### 4.2 Gerente de Recursos Humanos (HR_MANAGER)

- **Responsabilidades:** Alta/baja/actualización/consulta de empleados, asignación de área y gerente de área, aprobación de vacaciones de Gerentes de Área.
- **Rutas protegidas:** `role:HR_MANAGER|ADMIN`
  - Empleados: `GET/POST/PUT/DELETE /employees`, `employees/create`, `employees/{id}`, `employees/{id}/edit`
  - Áreas: recurso completo `areas` (listar, crear, editar, asignar gerente)
- **Aprobaciones:** Ve solo solicitudes asignadas a él (`assigned_approver_id = auth()->id()`), es decir, las de gerentes que solicitan vacaciones.
- **Navbar:** Ve Dashboard, Empleados, Áreas, Solicitudes, Aprobaciones, Notificaciones.

### 4.3 Gerentes de área (AREA_MANAGER)

- **Responsabilidades:** Autorizar o rechazar solicitudes de vacaciones **solo de los empleados de su área**.
- **Restricciones:** Un gerente solo puede aprobar solicitudes asignadas a él (resuelto por `assigned_approver_id`).
- **Rutas protegidas:** `role:AREA_MANAGER|HR_MANAGER|ADMIN`
  - Aprobaciones: `GET /approvals`, `POST /approvals/approve`, `POST /approvals/reject`
- **Filtro:** En aprobaciones solo se listan solicitudes con `assigned_approver_id = usuario actual`.
- **Navbar:** Ve Dashboard, Solicitudes, Aprobaciones, Notificaciones (no ve Empleados ni Áreas).

### 4.4 Empleados (EMPLOYEE)

- **Responsabilidades:** Solicitar vacaciones, consultar sus solicitudes, consultar días disponibles.
- **Rutas:** Mismas rutas de solicitudes que el resto (`vacation-requests`), con filtro en controlador.
- **Filtro:** En `vacation-requests.index` el controlador filtra por `employee_id` del empleado vinculado al usuario cuando **no** es HR_MANAGER ni ADMIN, de modo que solo ve sus propias solicitudes.
- **Navbar:** Ve Dashboard, Solicitudes, Notificaciones (no ve Empleados, Áreas ni Aprobaciones).

## Resumen de middleware en rutas

| Ruta / recurso      | Middleware              | Quién accede                    |
|---------------------|-------------------------|---------------------------------|
| `/dashboard`        | `auth`                  | Todos los autenticados          |
| `employees`         | `auth`, `role:HR_MANAGER\|ADMIN` | Solo RH y Admin                 |
| `areas`             | `auth`, `role:HR_MANAGER\|ADMIN` | Solo RH y Admin                 |
| `vacation-requests` | `auth`                  | Todos; listado filtrado por rol |
| `approvals`         | `auth`, `role:AREA_MANAGER\|HR_MANAGER\|ADMIN` | Solo aprobadores        |
| `notifications`     | `auth`                  | Todos los autenticados          |
| Asignar roles       | `auth`, `role:ADMIN`    | Solo Admin                      |

## Restricción “Solo 1 HR_MANAGER”

- Al asignar o añadir el rol `HR_MANAGER` (p. ej. en `assignRole` / `addRole`), se comprueba cuántos usuarios tienen ya ese rol.
- Si ya existe al menos uno y el usuario al que se asigna **no** tiene aún el rol, se devuelve error 422: *"Solo puede existir un Gerente de Recursos Humanos activo en el sistema."*

## Filtros en controladores

- **ApprovalController::index:** Solo solicitudes pendientes con `assigned_approver_id = Auth::id()`.
- **VacationRequestController::index:** Si el usuario no es HR_MANAGER ni ADMIN y tiene registro en `employees`, se filtra por su `employee_id` para que solo vea sus propias solicitudes.

## Navbar condicional

- **Empleados** y **Áreas:** solo si `auth()->user()?->hasRole(['HR_MANAGER', 'ADMIN'])`.
- **Aprobaciones:** solo si `auth()->user()?->hasRole(['AREA_MANAGER', 'HR_MANAGER', 'ADMIN'])`.
- Dashboard, Solicitudes y Notificaciones se muestran a todos los autenticados.
