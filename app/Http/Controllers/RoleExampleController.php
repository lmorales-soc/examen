<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Ejemplo de uso de roles y permisos con Spatie en controladores.
 * No usar en producción tal cual; adaptar a tu lógica de negocio.
 */
class RoleExampleController extends Controller
{
    /**
     * Solo usuarios con rol ADMIN.
     */
    public function adminOnly(): View
    {
        return view('examples.roles', [
            'message' => 'Solo usuarios con rol ADMIN pueden ver esta página.',
        ]);
    }

    /**
     * Solo usuarios con rol HR_MANAGER o ADMIN.
     */
    public function hrOrAdmin(): View
    {
        return view('examples.roles', [
            'message' => 'Solo HR_MANAGER o ADMIN pueden ver esta página.',
        ]);
    }

    /**
     * Solo usuarios con rol AREA_MANAGER, HR_MANAGER o ADMIN.
     */
    public function managersOnly(): View
    {
        return view('examples.roles', [
            'message' => 'Solo gestores (AREA_MANAGER, HR_MANAGER, ADMIN) pueden ver esta página.',
        ]);
    }

    /**
     * Cualquier usuario autenticado con rol EMPLOYEE o superior.
     */
    public function employeeArea(): View
    {
        return view('examples.roles', [
            'message' => 'Área para empleados y superiores.',
        ]);
    }

    /**
     * Asignar rol a un usuario (ejemplo; en producción validar y autorizar).
     */
    public function assignRole(Request $request): Response
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:ADMIN,HR_MANAGER,AREA_MANAGER,EMPLOYEE',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($request->role === 'HR_MANAGER') {
            $hrCount = User::role('HR_MANAGER')->count();
            if ($hrCount >= 1 && ! $user->hasRole('HR_MANAGER')) {
                return response('Solo puede existir un Gerente de Recursos Humanos activo en el sistema.', 422);
            }
        }

        $user->syncRoles([$request->role]);

        return response('Rol asignado correctamente.', 200);
    }

    /**
     * Añadir rol sin quitar los existentes.
     * Restricción 4.1: solo puede existir 1 HR_MANAGER en el sistema.
     */
    public function addRole(Request $request): Response
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:ADMIN,HR_MANAGER,AREA_MANAGER,EMPLOYEE',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($request->role === 'HR_MANAGER') {
            $hrCount = User::role('HR_MANAGER')->count();
            if ($hrCount >= 1 && ! $user->hasRole('HR_MANAGER')) {
                return response('Solo puede existir un Gerente de Recursos Humanos activo en el sistema.', 422);
            }
        }

        $user->assignRole($request->role);

        return response('Rol añadido correctamente.', 200);
    }

    /**
     * Comprobar rol en código (alternativa a middleware).
     */
    public function checkRoleInCode(Request $request): View|Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401, 'No autenticado.');
        }

        if ($user->hasRole('ADMIN')) {
            return view('examples.roles', ['message' => 'Eres ADMIN.']);
        }

        if ($user->hasAnyRole(['HR_MANAGER', 'AREA_MANAGER'])) {
            return view('examples.roles', ['message' => 'Eres gestor (HR o Área).']);
        }

        if ($user->hasRole('EMPLOYEE')) {
            return view('examples.roles', ['message' => 'Eres EMPLOYEE.']);
        }

        abort(403, 'No tienes un rol asignado.');
    }
}
