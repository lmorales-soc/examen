<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectVacationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'request_id' => ['required', 'integer', 'exists:vacation_requests,id'],
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
