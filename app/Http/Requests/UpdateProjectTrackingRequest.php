<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:milestone,progress,incident'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de registro es obligatorio.',
            'type.in' => 'El tipo de registro seleccionado no es válido.',
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de caracteres.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'date.required' => 'La fecha es obligatoria.',
            'date.date' => 'La fecha debe ser una fecha válida.',
        ];
    }
}
