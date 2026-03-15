# Casos de uso – Sistema de vacaciones (Clean Architecture)

Estructura de la capa **Application**: DTOs y Use Cases organizados por dominio.

---

## Estructura de carpetas

```
app/
├── Application/
│   ├── DTOs/
│   │   ├── CreateEmployeeDTO.php
│   │   ├── UpdateEmployeeDTO.php
│   │   ├── DeactivateEmployeeDTO.php
│   │   ├── AssignAreaManagerDTO.php
│   │   ├── CreateVacationRequestDTO.php
│   │   ├── ApproveVacationRequestDTO.php
│   │   ├── RejectVacationRequestDTO.php
│   │   ├── GetPendingRequestsDTO.php
│   │   └── GetVacationCalendarDTO.php
│   └── UseCases/
│       ├── Employee/
│       │   ├── CreateEmployeeUseCase.php
│       │   ├── UpdateEmployeeUseCase.php
│       │   ├── DeactivateEmployeeUseCase.php
│       │   └── AssignAreaManagerUseCase.php
│       └── VacationRequest/
│           ├── CreateVacationRequestUseCase.php
│           ├── ApproveVacationRequestUseCase.php
│           ├── RejectVacationRequestUseCase.php
│           ├── GetPendingRequestsUseCase.php
│           └── GetVacationCalendarUseCase.php
└── Domain/
    ├── Repositories/
    │   ├── EmployeeRepositoryInterface.php
    │   ├── AreaRepositoryInterface.php
    │   ├── VacationRequestRepositoryInterface.php
    │   └── UserRepositoryInterface.php
    ├── Rules/
    │   ├── WeekendExclusionRule.php
    │   ├── MaxConsecutiveDaysRule.php
    │   └── AvailableDaysRule.php
    ├── Services/
    │   └── VacationDaysCalculator.php
    └── Exceptions/
        └── ...
```

---

## Responsabilidades por caso de uso

| Use Case | DTO de entrada | Validaciones / reglas | Repositorios | Lógica principal |
|----------|----------------|------------------------|--------------|------------------|
| **CreateEmployee** | CreateEmployeeDTO | — | EmployeeRepository | Calcula días anuales por antigüedad (VacationDaysCalculator) si no se pasan; crea empleado. |
| **UpdateEmployee** | UpdateEmployeeDTO | Empleado existe | EmployeeRepository | Actualiza solo los campos no nulos del DTO. |
| **DeactivateEmployee** | DeactivateEmployeeDTO | Empleado existe | EmployeeRepository | Llama a deactivate(employeeId). |
| **AssignAreaManager** | AssignAreaManagerDTO | Área existe | AreaRepository, UserRepository | Asigna rol AREA_MANAGER al usuario y setAreaManager(areaId, userId). |
| **CreateVacationRequest** | CreateVacationRequestDTO | Max 6 días consecutivos; días hábiles > 0; días solicitados ≤ disponibles; empleado existe | VacationRequestRepository, EmployeeRepository, VacationDaysCalculator, MaxConsecutiveDaysRule, AvailableDaysRule | Calcula días hábiles (sin fines de semana), valida reglas, crea solicitud en estado `pending`. |
| **ApproveVacationRequest** | ApproveVacationRequestDTO | Solicitud existe y está `pending` | VacationRequestRepository | Cambia estado a `approved`; el saldo se deriva de solicitudes aprobadas en el repositorio. |
| **RejectVacationRequest** | RejectVacationRequestDTO | Solicitud existe y está `pending` | VacationRequestRepository | Cambia estado a `rejected` y guarda motivo. |
| **GetPendingRequests** | GetPendingRequestsDTO | — | VacationRequestRepository | Devuelve getPending(employeeId?, areaId?). |
| **GetVacationCalendar** | GetVacationCalendarDTO | — | VacationRequestRepository | Devuelve getForCalendar(year, areaId?) para calendario. |

---

## Flujo por caso de uso

### CreateEmployee

1. Recibe **CreateEmployeeDTO** (userId, areaId, employeeNumber, firstName, lastName, hireDate, vacationDaysAnnual opcional).
2. Si no viene `vacationDaysAnnual`, calcula antigüedad con `VacationDaysCalculator::yearsOfService` y días con `getAnnualDaysBySeniority`.
3. Llama a **EmployeeRepository::create** con los datos.

### UpdateEmployee

1. Recibe **UpdateEmployeeDTO** (id + campos opcionales).
2. Comprueba que el empleado exista con **EmployeeRepository::findById**.
3. Filtra campos no nulos y llama a **EmployeeRepository::update**.

### DeactivateEmployee

1. Recibe **DeactivateEmployeeDTO** (employeeId).
2. Comprueba que el empleado exista.
3. Llama a **EmployeeRepository::deactivate(employeeId)**.

### AssignAreaManager

1. Recibe **AssignAreaManagerDTO** (areaId, userId).
2. Comprueba que el área exista.
3. **UserRepository::assignRole(userId, 'AREA_MANAGER')**.
4. **AreaRepository::setAreaManager(areaId, userId)**.

### CreateVacationRequest

1. Recibe **CreateVacationRequestDTO** (employeeId, startDate, endDate, comments).
2. **MaxConsecutiveDaysRule::validate(start, end)** (máx. 6 días consecutivos).
3. **VacationDaysCalculator::getBusinessDaysInRange(start, end)** → días hábiles (sin sáb/dom).
4. **EmployeeRepository::getAvailableVacationDays(employeeId, year)**.
5. **AvailableDaysRule::validate(requestedDays, availableDays)**.
6. Comprueba que el empleado exista.
7. **VacationRequestRepository::create** con status `pending` y `days_requested` = días hábiles.

### ApproveVacationRequest

1. Recibe **ApproveVacationRequestDTO** (requestId, approvedByUserId).
2. Comprueba que la solicitud exista y esté en `pending`.
3. **VacationRequestRepository::updateStatus(id, 'approved', approvedByUserId, null)**.

### RejectVacationRequest

1. Recibe **RejectVacationRequestDTO** (requestId, rejectedByUserId, rejectionReason).
2. Comprueba que la solicitud exista y esté en `pending`.
3. **VacationRequestRepository::updateStatus(id, 'rejected', rejectedByUserId, rejectionReason)**.

### GetPendingRequests

1. Recibe **GetPendingRequestsDTO** (employeeId opcional, areaId opcional).
2. Devuelve **VacationRequestRepository::getPending(employeeId, areaId)**.

### GetVacationCalendar

1. Recibe **GetVacationCalendarDTO** (year, areaId opcional).
2. Devuelve **VacationRequestRepository::getForCalendar(year, areaId)**.

---

## Dependencias

- **Application** depende de **Domain** (interfaces de repositorios, reglas, servicios).
- Los Use Cases **no** conocen Infrastructure (Eloquent, HTTP). Los repositorios se inyectan por interfaz.
- Los controladores (Http) reciben el request, construyen el DTO, llaman al Use Case e interpretan el resultado (vista/JSON).
