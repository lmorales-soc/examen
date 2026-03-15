<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleExampleController;
use App\Http\Controllers\VacationRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Dashboard: solo ADMIN y HR_MANAGER ven contenido; gerentes/empleados se redirigen
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Gestión de Empleados: solo RH y ADMIN (4.2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:HR_MANAGER|ADMIN'])->prefix('employees')->name('employees.')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('create');
    Route::post('/', [EmployeeController::class, 'store'])->name('store');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Gestión de Áreas (asignar gerente, etc.): solo RH y ADMIN (4.2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:HR_MANAGER|ADMIN'])->resource('areas', AreaController::class);

/*
|--------------------------------------------------------------------------
| Solicitudes de vacaciones: todos los autenticados (empleados solicitan, RH/ADMIN consultan)
| El controlador filtra: empleados ven solo las suyas (4.4)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('vacation-requests')->name('vacation-requests.')->group(function () {
    Route::get('/', [VacationRequestController::class, 'index'])->name('index');
    Route::get('/create', [VacationRequestController::class, 'create'])->name('create');
    Route::post('/', [VacationRequestController::class, 'store'])->name('store');
    Route::get('/calendar', [VacationRequestController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [VacationRequestController::class, 'calendarEvents'])->name('calendar.events');
});

/*
|--------------------------------------------------------------------------
| Aprobaciones: solo Gerentes de Área, RH y ADMIN (4.3, 4.2)
| Cada uno ve solo las solicitudes asignadas a él (assigned_approver_id)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:AREA_MANAGER|HR_MANAGER|ADMIN'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('index');
    Route::post('/approve', [ApprovalController::class, 'approve'])->name('approve');
    Route::post('/reject', [ApprovalController::class, 'reject'])->name('reject');
});

/*
|--------------------------------------------------------------------------
| Notificaciones: todos los autenticados
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
});

/*
|--------------------------------------------------------------------------
| Rutas solo ADMIN (crear primer RH, configuración, asignar roles) (4.1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/admin-only', [RoleExampleController::class, 'adminOnly'])->name('roles.admin');
    Route::post('/assign-role', [RoleExampleController::class, 'assignRole'])->name('roles.assign');
    Route::post('/add-role', [RoleExampleController::class, 'addRole'])->name('roles.add');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/check-role-in-code', [RoleExampleController::class, 'checkRoleInCode'])->name('roles.check');
    Route::get('/hr-or-admin', [RoleExampleController::class, 'hrOrAdmin'])->middleware('role:HR_MANAGER|ADMIN')->name('roles.hr');
    Route::get('/managers-only', [RoleExampleController::class, 'managersOnly'])->middleware('role:AREA_MANAGER|HR_MANAGER|ADMIN')->name('roles.managers');
    Route::get('/employee-area', [RoleExampleController::class, 'employeeArea'])->middleware('role:EMPLOYEE|AREA_MANAGER|HR_MANAGER|ADMIN')->name('roles.employee');
});
