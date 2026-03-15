<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $areaId = $this->route('area');

        return [
            'name' => ['required', 'string', 'max:100', 'unique:areas,name,' . $areaId],
            'slug' => ['nullable', 'string', 'max:100', 'unique:areas,slug,' . $areaId, 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del área es obligatorio.',
            'name.unique' => 'Ya existe otro área con ese nombre.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
        ];
    }
}
