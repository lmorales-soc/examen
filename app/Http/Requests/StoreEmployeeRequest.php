<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                'in:EMPLOYEE,AREA_MANAGER,HR_MANAGER',
            ],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'employee_number' => ['required', 'string', 'max:50', 'unique:employees,employee_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'hire_date' => ['required', 'date'],
            'vacation_days_annual' => ['nullable', 'integer', 'min:0', 'max:365'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('role') === 'HR_MANAGER') {
                $hrCount = User::role('HR_MANAGER')->count();
                if ($hrCount >= 1) {
                    $validator->errors()->add(
                        'role',
                        'Solo puede existir un Gerente de Recursos Humanos activo en el sistema.'
                    );
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del usuario es obligatorio.',
            'email.required' => 'El correo es obligatorio para el acceso al sistema.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.required' => 'Debe asignar un rol al empleado.',
            'role.in' => 'El rol debe ser Empleado, Gerente de Área o Gerente de RH.',
        ];
    }
}
