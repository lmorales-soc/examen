<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class StoreVacationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if ($userId === null) {
                        $fail('Debe iniciar sesión.');
                        return;
                    }
                    $employee = Employee::where('id', $value)->where('user_id', $userId)->first();
                    if (! $employee) {
                        $fail('Solo puede solicitar vacaciones para su propio registro de empleado.');
                    }
                },
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
