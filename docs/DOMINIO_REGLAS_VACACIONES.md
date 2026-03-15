# Reglas de negocio de vacaciones – Capa Domain

Documentación de las clases de dominio y su interacción.

---

## Clases implementadas

| Clase | Ubicación | Responsabilidad |
|-------|-----------|------------------|
| **VacationDaysCalculator** | `App\Domain\Services\VacationDaysCalculator` | Días anuales por antigüedad y días hábiles en un rango (usa WeekendExclusionRule). |
| **WeekendExclusionRule** | `App\Domain\Rules\WeekendExclusionRule` | Cuenta solo lunes–viernes en un rango; no cuenta sábados ni domingos. |
| **MaxConsecutiveDaysRule** | `App\Domain\Rules\MaxConsecutiveDaysRule` | Valida que el rango no supere 6 días consecutivos (naturales). |
| **AvailableDaysRule** | `App\Domain\Rules\AvailableDaysRule` | Valida que los días solicitados no superen los disponibles. |

Excepciones: `InsufficientVacationDaysException`, `TooManyConsecutiveDaysException` en `App\Domain\Exceptions`.

---

## Cómo interactúan entre ellas

### Flujo al crear/validar una solicitud de vacaciones

1. **Fechas de la solicitud**  
   El usuario envía `start_date` y `end_date`.

2. **MaxConsecutiveDaysRule**  
   Se valida que el rango en días naturales (inclusive) sea ≤ 6.  
   - Entrada: `start_date`, `end_date`.  
   - Si no se cumple → `TooManyConsecutiveDaysException`.

3. **WeekendExclusionRule (vía VacationDaysCalculator)**  
   Se calcula cuántos días **hábiles** hay en ese rango (sin sábados ni domingos).  
   - Entrada: `start_date`, `end_date`.  
   - Salida: `requestedBusinessDays` (número que se descontará al aprobar).

4. **VacationDaysCalculator**  
   - `getBusinessDaysInRange(start, end)` usa internamente `WeekendExclusionRule::countBusinessDays()` y devuelve ese valor.  
   - Para el saldo anual del empleado se usa `getAnnualDaysBySeniority(yearsOfService)` (tabla de antigüedad).

5. **AvailableDaysRule**  
   Se valida que los días que se van a solicitar no superen el saldo disponible.  
   - Entrada: `requestedBusinessDays` (del paso 3), `availableDays` (saldo actual del empleado).  
   - Si `requestedBusinessDays > availableDays` → `InsufficientVacationDaysException`.

6. **Descuento de días**  
   Los días **no** se descuentan al crear la solicitud. Se descuentan solo cuando la solicitud pasa a estado **aprobada** (lógica en Application/Infrastructure al aprobar).

Resumen de dependencias:

- **VacationDaysCalculator** depende de **WeekendExclusionRule** (para contar días hábiles).
- **MaxConsecutiveDaysRule** y **AvailableDaysRule** son independientes entre sí y del calculador.
- La **orquestación** (llamar en orden a MaxConsecutiveDaysRule → calcular días hábiles → AvailableDaysRule) corresponde a un **caso de uso** en la capa Application, que usa estas clases del dominio.

---

## Reglas de negocio cubiertas

| Regla | Clase(s) |
|-------|----------|
| Días por antigüedad (1→12 … 10→24) | VacationDaysCalculator::getAnnualDaysBySeniority() |
| No solicitar más días de los disponibles | AvailableDaysRule |
| Los días se descuentan solo al aprobar | No es dominio; se aplica al cambiar estado a "approved" en la aplicación. |
| No contar sábados ni domingos | WeekendExclusionRule (y VacationDaysCalculator::getBusinessDaysInRange()) |
| Máximo 6 días consecutivos | MaxConsecutiveDaysRule |

---

## Uso desde un caso de uso (ejemplo)

En la capa Application, al crear una solicitud de vacaciones:

1. Obtener `availableDays` del empleado (p. ej. desde repositorio o servicio de saldo).
2. `MaxConsecutiveDaysRule->validate($start, $end)`.
3. `$requestedDays = $vacationDaysCalculator->getBusinessDaysInRange($start, $end)`.
4. `AvailableDaysRule->validate($requestedDays, $availableDays)`.
5. Persistir la solicitud con `days_requested = $requestedDays` y estado `pending`.  
6. Al **aprobar** la solicitud, descontar `days_requested` del saldo del empleado.
