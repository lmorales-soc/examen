# Documentación técnica – Sistema de gestión de vacaciones

**Versión:** 1.0  
**Alcance:** Arquitectura, modelo de datos, reglas de negocio, flujo de aprobación y módulos del sistema.

---

## 1. Introducción

El **Sistema de gestión de vacaciones** permite a los empleados solicitar vacaciones, asigna automáticamente un aprobador (gerente de área o RH según el rol del solicitante) y notifica por correo y notificación interna. Las reglas de negocio (días por antigüedad, máximo de días consecutivos, exclusión de fines de semana y saldo disponible) se aplican en la capa de dominio y casos de uso.

**Stack técnico:** Laravel 10+, PHP 8.2+, MySQL, Bootstrap 5, Blade, spatie/laravel-permission, FullCalendar, Laravel Notifications.

---

## 2. Arquitectura

El sistema sigue **Clean Architecture**: las dependencias apuntan hacia el dominio; la lógica de negocio no depende de frameworks ni de infraestructura.

### 2.1 Capas

| Capa | Ubicación | Responsabilidad |
|------|-----------|------------------|
| **Domain** | `app/Domain/` | Entidades conceptuales, reglas de negocio, excepciones de dominio e **interfaces** de repositorios. No depende de ninguna otra capa. |
| **Application** | `app/Application/` | Casos de uso (orquestación), DTOs y servicios de aplicación. Depende solo del Domain. |
| **Infrastructure** | `app/Infrastructure/` (o implementaciones en `app/`) | Implementaciones de repositorios (Eloquent), notificaciones, adaptadores. Implementa contratos del Domain. |
| **Http** | `app/Http/` | Controladores, Form Requests, respuestas web/API. Depende de Application (use cases) y opcionalmente del Domain. |

### 2.2 Flujo de dependencias

```
                    ┌─────────────────────────────────────────┐
                    │                  HTTP                    │
                    │  Controllers · Form Requests · Views     │
                    └────────────────────┬────────────────────┘
                                         │ usa
                                         ▼
                    ┌─────────────────────────────────────────┐
                    │              APPLICATION                 │
                    │  Use Cases · DTOs · ApprovalResolver    │
                    └────────────────────┬────────────────────┘
                                         │ usa interfaces
                                         ▼
                    ┌─────────────────────────────────────────┐
                    │                DOMAIN                   │
                    │  Rules · Services · Exceptions ·        │
                    │  Repository Interfaces                   │
                    └────────────────────┬────────────────────┘
                                         ▲
                                         │ implementa
                    ┌────────────────────┴────────────────────┐
                    │            INFRASTRUCTURE               │
                    │  Eloquent Repositories · Notifications  │
                    └─────────────────────────────────────────┘
```

### 2.3 Estructura de carpetas relevante

```
app/
├── Domain/
│   ├── Entities/          (conceptual; en este proyecto se usan arrays/DTOs)
│   ├── Exceptions/        DomainException, InsufficientVacationDaysException,
│   │                      TooManyConsecutiveDaysException, UnauthorizedApprovalException
│   ├── Repositories/      Interfaces: Employee, Area, VacationRequest, User
│   ├── Rules/             WeekendExclusionRule, MaxConsecutiveDaysRule, AvailableDaysRule
│   └── Services/          VacationDaysCalculator
├── Application/
│   ├── DTOs/              Objetos de transferencia para cada caso de uso
│   ├── Services/          ApprovalResolverService
│   └── UseCases/
│       ├── Employee/      Create, Update, Deactivate, AssignAreaManager, List, Get
│       ├── Area/          ListAreas
│       └── VacationRequest/ Create, Approve, Reject, GetPending, GetVacationCalendar
├── Http/
│   ├── Controllers/       Employee, Area, VacationRequest, Approval, Dashboard, Notification
│   └── Requests/          Form Requests de validación
├── Notifications/         VacationRequestCreatedNotification (mail + database)
└── Models/                User, Eloquent models si se usan
```

---

## 3. Modelo de datos

### 3.1 Tablas principales

| Tabla | Descripción |
|-------|-------------|
| **users** | Usuarios del sistema (autenticación). Relación 1:1 con empleados cuando el usuario es empleado. |
| **roles** / **permissions** / **model_has_roles** | Spatie: roles (ADMIN, HR_MANAGER, AREA_MANAGER, EMPLOYEE) y permisos. |
| **areas** | Áreas/departamentos (Desarrollo, QA, Infraestructura, Base de Datos, Pagos). Incluye referencia al gerente del área. |
| **employees** | Empleados: user_id, area_id, employee_number, first_name, last_name, hire_date, vacation_days_annual. |
| **vacation_requests** | Solicitudes: employee_id, start_date, end_date, days_requested, status, approved_by, approved_at, rejection_reason, comments, assigned_approver_id. |
| **notifications** | Notificaciones en base de datos (Laravel): notifiable_type, notifiable_id, type, data, read_at. |

### 3.2 Relaciones

- **users** 1:1 **employees** (employees.user_id → users.id).
- **areas** 1:N **employees** (employees.area_id → areas.id).
- **employees** 1:N **vacation_requests** (vacation_requests.employee_id → employees.id).
- **users** N:1 **vacation_requests** como aprobador (vacation_requests.approved_by → users.id; assigned_approver_id → users.id).
- **notifications**: relación polimórfica con users (notifiable_type = User, notifiable_id = user.id).

### 3.3 Estados de solicitud

- **pending**: recién creada, pendiente de aprobación.
- **approved**: aprobada; los días se consideran consumidos para el cálculo de saldo.
- **rejected**: rechazada (con rejection_reason).
- **cancelled**: cancelada.

### 3.4 Restricciones relevantes

- `employees.user_id` UNIQUE (un usuario solo puede ser un empleado).
- `employees.employee_number` UNIQUE.
- `vacation_requests`: status en conjunto permitido; approved_by y assigned_approver_id referencian users.
- Los días disponibles se calculan por año a partir de vacation_days_annual y de las solicitudes aprobadas (no se almacena un campo “días consumidos” en employees; la implementación del repositorio puede derivarlo).

---

## 4. Reglas de negocio

### 4.1 Días de vacaciones por antigüedad

Años completos desde la fecha de alta → días anuales asignados:

| Años | Días anuales |
|------|--------------|
| 1    | 12           |
| 2    | 14           |
| 3    | 16           |
| 4    | 18           |
| 5    | 20           |
| 6–9  | 22           |
| 10+  | 24           |

Implementación: **VacationDaysCalculator::getAnnualDaysBySeniority()**.

### 4.2 Restricciones sobre solicitudes

- **No solicitar más días de los disponibles.**  
  Implementación: **AvailableDaysRule**. El saldo disponible se obtiene vía repositorio (asignados menos días de solicitudes aprobadas en el año).

- **Los días se descuentan solo cuando la solicitud es aprobada.**  
  No es una regla de dominio sino de proceso: al pasar una solicitud a estado `approved`, el sistema considera esos días consumidos (normalmente en la implementación del repositorio o en un caso de uso de aprobación).

- **No contar sábados ni domingos.**  
  Solo se cuentan días hábiles (lunes–viernes) en el rango de fechas.  
  Implementación: **WeekendExclusionRule** y **VacationDaysCalculator::getBusinessDaysInRange()**.

- **Máximo 6 días consecutivos por solicitud.**  
  El rango en días naturales (inclusive) no puede superar 6.  
  Implementación: **MaxConsecutiveDaysRule**.

### 4.3 Clases de dominio implicadas

| Regla | Clase(s) |
|-------|----------|
| Días por antigüedad | VacationDaysCalculator::getAnnualDaysBySeniority() |
| Días solicitados ≤ disponibles | AvailableDaysRule |
| Solo días hábiles | WeekendExclusionRule, VacationDaysCalculator::getBusinessDaysInRange() |
| Máximo 6 días consecutivos | MaxConsecutiveDaysRule |

---

## 5. Flujo de aprobación

### 5.1 Reglas de asignación del aprobador

- **Si un EMPLEADO solicita vacaciones**  
  La solicitud la aprueba o rechaza el **gerente del área** del empleado.

- **Si un GERENTE solicita vacaciones**  
  (usuario con rol AREA_MANAGER, HR_MANAGER o ADMIN)  
  La solicitud la aprueba **RH** (primer usuario con rol HR_MANAGER o ADMIN).

- **ADMIN** puede aprobar o rechazar cualquier solicitud, con independencia del flujo anterior.

### 5.2 Componente central: ApprovalResolverService

Ubicación: `Application/Services/ApprovalResolverService`.

| Método | Descripción |
|--------|-------------|
| getApproverTypeForRequest($request) | Devuelve `'area_manager'` o `'hr'` según el rol del solicitante (empleado vs gerente). |
| getApproverUserIdForRequest($request) | Devuelve el `user_id` del aprobador (gerente del área o primer HR/ADMIN). |
| canUserApproveRequest($request, $userId) | Comprueba si el usuario puede aprobar (ADMIN siempre; si no, debe ser el aprobador resuelto). |
| authorizeApproval($request, $userId) | Valida; lanza **UnauthorizedApprovalException** si el usuario no está autorizado. |

Depende de: **EmployeeRepositoryInterface**, **AreaRepositoryInterface**, **UserRepositoryInterface**.

### 5.3 Integración en casos de uso

- **CreateVacationRequestUseCase**  
  Tras validar, obtiene el aprobador con `ApprovalResolverService::getApproverUserIdForRequest()` y persiste `assigned_approver_id` en la solicitud. La notificación al aprobador se dispara desde el controlador tras la creación.

- **ApproveVacationRequestUseCase**  
  Antes de cambiar el estado, llama a `ApprovalResolverService::authorizeApproval($request, $approvedByUserId)`. Si no lanza, actualiza la solicitud a `approved`.

- **RejectVacationRequestUseCase**  
  Igual: `authorizeApproval($request, $rejectedByUserId)` y luego actualiza a `rejected` con motivo.

### 5.4 Notificación al aprobador

Al crear una solicitud con `assigned_approver_id`, el **VacationRequestController** notifica a ese usuario mediante **VacationRequestCreatedNotification** (canales: **mail** y **database**), incluyendo nombre del empleado, fechas y días.

---

## 6. Módulos del sistema

### 6.1 Módulo de empleados

- **Casos de uso:** CreateEmployee, UpdateEmployee, DeactivateEmployee, AssignAreaManager, ListEmployees, GetEmployee.
- **Controlador:** EmployeeController (CRUD y listado).
- **Reglas:** Cálculo de días anuales por antigüedad al crear empleado (VacationDaysCalculator).

### 6.2 Módulo de áreas

- **Casos de uso:** ListAreas (y asignación de gerente vía AssignAreaManager).
- **Controlador:** AreaController (listado).
- **Persistencia:** Tabla `areas`; gerente del área necesario para el flujo de aprobación.

### 6.3 Módulo de solicitudes de vacaciones

- **Casos de uso:** CreateVacationRequest, GetPendingRequests, GetVacationCalendar.
- **Controlador:** VacationRequestController (crear, listar, calendario, endpoint JSON para FullCalendar).
- **Reglas:** MaxConsecutiveDaysRule, AvailableDaysRule, VacationDaysCalculator (días hábiles), asignación de aprobador y notificación.

### 6.4 Módulo de aprobaciones

- **Casos de uso:** ApproveVacationRequest, RejectVacationRequest; listado de pendientes (GetPendingRequests).
- **Controlador:** ApprovalController (listado de pendientes, aprobar, rechazar).
- **Reglas:** Solo el aprobador resuelto (o ADMIN) puede aprobar/rechazar (ApprovalResolverService).

### 6.5 Módulo de notificaciones

- **Laravel Notifications:** VacationRequestCreatedNotification (mail + database).
- **Controlador:** NotificationController (listado de notificaciones del usuario, marcar como leída, marcar todas como leídas).
- **Persistencia:** Tabla `notifications` (Laravel estándar). Enlace en la barra de navegación con contador de no leídas.

### 6.6 Módulo de calendario

- **Endpoint:** GET `vacation-requests/calendar/events` (JSON) con parámetros `start`, `end`, opcionalmente `area_id`. Devuelve eventos en formato FullCalendar (título: nombre empleado · área; extendedProps: employeeName, area, daysRequested).
- **Vista:** Calendario FullCalendar (vista mes/semana/lista) que consume el endpoint y muestra vacaciones aprobadas con nombre del empleado, área y fechas.

### 6.7 Autenticación y roles

- **Spatie Laravel Permission:** roles ADMIN, HR_MANAGER, AREA_MANAGER, EMPLOYEE.
- **Middleware:** `role:...` en rutas para restringir acceso por rol.
- **User:** trait Notifiable (notificaciones) y HasRoles (Spatie).

---

## 7. Resumen de flujos principales

1. **Alta de empleado:** Formulario → StoreEmployeeRequest → CreateEmployeeDTO → CreateEmployeeUseCase (cálculo de días por antigüedad si no se indican) → persistencia → redirección.
2. **Solicitud de vacaciones:** Formulario → StoreVacationRequestRequest → CreateVacationRequestDTO → CreateVacationRequestUseCase (validación de reglas, asignación de aprobador) → persistencia → notificación al aprobador (mail + DB) → redirección.
3. **Aprobación/Rechazo:** ApprovalController → ApproveVacationRequestDTO / RejectVacationRequestDTO → ApproveVacationRequestUseCase / RejectVacationRequestUseCase (authorizeApproval + actualización de estado) → redirección.
4. **Consulta de notificaciones:** NotificationController → listado y marcar leídas; enlace desde la barra de navegación.
5. **Calendario:** Vista FullCalendar → petición AJAX a `vacation-requests/calendar/events` → VacationRequestController::calendarEvents (GetVacationCalendarUseCase + enriquecimiento con nombre y área) → JSON → renderizado de eventos.

---

## 8. Referencias a documentación detallada

- **Roles y permisos:** `docs/ROLES_SPATIE.md`
- **Casos de uso y DTOs:** `docs/APPLICATION_USECASES.md`
- **Reglas de dominio (vacaciones):** `docs/DOMINIO_REGLAS_VACACIONES.md`
- **Flujo de aprobación (componentes y persistencia):** `docs/FLUJO_APROBACION.md`

---

*Documento generado para el Sistema de gestión de vacaciones. Para dudas sobre implementación concreta, consultar el código en las capas Domain, Application y Http indicadas.*
