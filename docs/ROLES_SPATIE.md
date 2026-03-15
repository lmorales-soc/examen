# Sistema de roles con spatie/laravel-permission

Documentación del sistema de roles (ADMIN, HR_MANAGER, AREA_MANAGER, EMPLOYEE) implementado con **spatie/laravel-permission**.

---

## 1. Instalación y configuración

### 1.1 Instalación del paquete

```bash
composer require spatie/laravel-permission
```

En proyectos con PHP 8.2 se instalará una versión compatible (ej. 6.x). Para PHP 8.4+ está disponible la 7.x.

### 1.2 Publicar configuración y migraciones (opcional)

Si el paquete no publica automáticamente:

```bash
php artisan vendor:publish --tag=permission-config
php artisan vendor:publish --tag=permission-migrations
```

En este proyecto la **config** y la **migración** se han añadido manualmente:

- **Config:** `config/permission.php`  
  Define modelos (Role, Permission), nombres de tablas, columnas y caché.

- **Migración:** `database/migrations/2025_03_13_090000_create_permission_tables.php`  
  Crea las tablas: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

### 1.3 Modelo User

El modelo `User` debe usar el trait `HasRoles`:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;
    // ...
}
```

Con esto el usuario puede:

- `$user->assignRole('ADMIN')`
- `$user->hasRole('ADMIN')`
- `$user->hasAnyRole(['HR_MANAGER', 'ADMIN'])`
- `$user->getRoleNames()`
- etc.

---

## 2. Migraciones necesarias

Orden de ejecución recomendado:

1. Migraciones por defecto de Laravel (`users`, `sessions`, etc.).
2. **Migración de Spatie:** `2025_03_13_090000_create_permission_tables.php`.
3. Resto de migraciones del proyecto (areas, employees, vacation_requests, notifications).

Ejecutar todas:

```bash
php artisan migrate
```

Si ya existían tablas de Spatie, no es necesario volver a publicar; la migración incluida en el proyecto ya crea las tablas con la estructura esperada por el paquete.

---

## 3. Seeders para crear los roles

### RoleSeeder

El seeder `database/seeders/RoleSeeder.php` crea los cuatro roles obligatorios:

- **ADMIN**
- **HR_MANAGER**
- **AREA_MANAGER**
- **EMPLOYEE**

Usa el guard por defecto de la aplicación (`config('auth.defaults.guard')`, normalmente `web`).

Ejecutar solo el seeder de roles:

```bash
php artisan db:seed --class=RoleSeeder
```

O ejecutar todos los seeders (incluye RoleSeeder si está registrado en `DatabaseSeeder`):

```bash
php artisan db:seed
```

En `DatabaseSeeder` se ha añadido:

```php
$this->call([
    RoleSeeder::class,
]);
```

---

## 4. Middleware para control de acceso

### 4.1 Registro en bootstrap/app.php

En Laravel 11/12 el registro de middleware se hace en `bootstrap/app.php`:

```php
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    // ...
```

### 4.2 Uso del middleware

- **role:** exige uno o más roles (separados por `|`).
- **permission:** exige uno o más permisos (si se usan permisos además de roles).
- **role_or_permission:** exige al menos un rol o un permiso.

Ejemplos en rutas:

```php
Route::get('/admin', ...)->middleware('role:ADMIN');
Route::get('/hr', ...)->middleware('role:HR_MANAGER|ADMIN');
Route::get('/manage', ...)->middleware('role_or_permission:AREA_MANAGER|edit-vacations');
```

Si el usuario no tiene el rol (o permiso), Spatie lanza `UnauthorizedException` (403).

---

## 5. Cómo asignar roles a usuarios

### 5.1 Asignar un solo rol (reemplaza los anteriores)

```php
$user = User::find(1);
$user->syncRoles(['EMPLOYEE']);
```

### 5.2 Asignar un rol sin quitar los demás

```php
$user->assignRole('HR_MANAGER');
```

### 5.3 Asignar varios roles a la vez

```php
$user->syncRoles(['EMPLOYEE', 'AREA_MANAGER']);
// o
$user->assignRole(['EMPLOYEE', 'AREA_MANAGER']);
```

### 5.4 Quitar un rol

```php
$user->removeRole('AREA_MANAGER');
```

### 5.5 Comprobar rol

```php
$user->hasRole('ADMIN');                    // true/false
$user->hasAnyRole(['HR_MANAGER', 'ADMIN']); // true si tiene al menos uno
$user->getRoleNames();                      // colección de nombres
```

### 5.6 En un seeder (ejemplo: usuario admin)

```php
$admin = User::firstOrCreate(
    ['email' => 'admin@example.com'],
    ['name' => 'Admin', 'password' => Hash::make('password')]
);
$admin->syncRoles(['ADMIN']);
```

---

## 6. Protección de rutas por rol

### 6.1 Rutas web

En `routes/web.php` se han definido rutas de ejemplo protegidas por rol:

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-only', [RoleExampleController::class, 'adminOnly'])
        ->middleware('role:ADMIN');

    Route::get('/hr-or-admin', [RoleExampleController::class, 'hrOrAdmin'])
        ->middleware('role:HR_MANAGER|ADMIN');

    Route::get('/managers-only', [RoleExampleController::class, 'managersOnly'])
        ->middleware('role:AREA_MANAGER|HR_MANAGER|ADMIN');

    Route::get('/employee-area', [RoleExampleController::class, 'employeeArea'])
        ->middleware('role:EMPLOYEE|AREA_MANAGER|HR_MANAGER|ADMIN');

    // Solo ADMIN puede asignar roles (ejemplo)
    Route::post('/assign-role', [RoleExampleController::class, 'assignRole'])
        ->middleware('role:ADMIN');
});
```

Regla: primero `auth`, luego `role:...` (o `permission:...`). Sin rol adecuado, el usuario recibe 403.

### 6.2 Rutas API

En `routes/api.php` se puede usar el mismo alias de middleware (si el guard API usa el mismo modelo User con HasRoles):

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/data', ...)->middleware('role:ADMIN');
});
```

Si usas otro guard, pasarlo como segundo argumento al middleware de Spatie (ver documentación del paquete).

---

## 7. Ejemplos de uso en controladores

El controlador de ejemplo está en `app/Http/Controllers/RoleExampleController.php`.

### 7.1 Rutas protegidas por middleware

Las acciones `adminOnly`, `hrOrAdmin`, `managersOnly`, `employeeArea` no comprueban el rol en código; el middleware `role:...` ya restringe el acceso. El controlador solo devuelve la vista.

### 7.2 Comprobar rol dentro del controlador

```php
public function checkRoleInCode(Request $request): View|Response
{
    $user = $request->user();
    if (! $user) {
        abort(401, 'No autenticado.');
    }
    if ($user->hasRole('ADMIN')) {
        return view('examples.roles', ['message' => 'Eres ADMIN.']);
    }
    if ($user->hasAnyRole(['HR_MANAGER', 'AREA_MANAGER'])) {
        return view('examples.roles', ['message' => 'Eres gestor.']);
    }
    if ($user->hasRole('EMPLOYEE')) {
        return view('examples.roles', ['message' => 'Eres EMPLOYEE.']);
    }
    abort(403, 'No tienes un rol asignado.');
}
```

### 7.3 Asignar rol (solo ADMIN en el ejemplo)

```php
public function assignRole(Request $request): Response
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'role' => 'required|string|in:ADMIN,HR_MANAGER,AREA_MANAGER,EMPLOYEE',
    ]);
    $user = User::findOrFail($request->user_id);
    $user->syncRoles([$request->role]);
    return response('Rol asignado correctamente.', 200);
}
```

### 7.4 En políticas (Policy)

Puedes combinar roles con políticas de Laravel:

```php
public function approve(User $user, VacationRequest $request): bool
{
    return $user->hasAnyRole(['ADMIN', 'HR_MANAGER', 'AREA_MANAGER']);
}
```

Y en el controlador:

```php
$this->authorize('approve', $vacationRequest);
```

---

## Resumen de archivos tocados

| Archivo | Descripción |
|--------|--------------|
| `composer.json` | Dependencia `spatie/laravel-permission` |
| `config/permission.php` | Configuración del paquete |
| `database/migrations/2025_03_13_090000_create_permission_tables.php` | Tablas de roles y permisos |
| `database/seeders/RoleSeeder.php` | Crea los 4 roles |
| `database/seeders/DatabaseSeeder.php` | Llama a RoleSeeder |
| `app/Models/User.php` | Trait HasRoles |
| `bootstrap/app.php` | Alias de middleware role, permission, role_or_permission |
| `routes/web.php` | Rutas de ejemplo protegidas por rol |
| `app/Http/Controllers/RoleExampleController.php` | Ejemplos de uso en controladores |
| `resources/views/examples/roles.blade.php` | Vista de ejemplo |

Tras `php artisan migrate` y `php artisan db:seed`, asigna al menos el rol ADMIN a un usuario para probar las rutas restringidas.
