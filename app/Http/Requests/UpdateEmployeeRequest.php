<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $employeeId = $this->route('employee');
        if (is_object($employeeId)) {
            $employeeId = $employeeId->id ?? $employeeId;
        }

        return [
            'area_id' => ['sometimes', 'integer', 'exists:areas,id'],
            'employee_number' => ['sometimes', 'string', 'max:50', 'unique:employees,employee_number,' . (int) $employeeId],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'hire_date' => ['sometimes', 'date'],
            'vacation_days_annual' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
        ];
    }
}
