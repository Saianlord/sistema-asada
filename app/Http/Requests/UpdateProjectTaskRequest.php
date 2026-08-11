<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'assigned_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'status' => ['required', 'string', 'in:pending,in_progress,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de caracteres.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'due_date.required' => 'La fecha compromiso es obligatoria.',
            'due_date.date' => 'La fecha compromiso debe ser una fecha válida.',
            'assigned_user_id.required' => 'El responsable es obligatorio.',
            'assigned_user_id.exists' => 'El responsable seleccionado no es válido o no está activo.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
