<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technical_score' => ['required', 'integer', 'min:1', 'max:10'],
            'financial_score' => ['required', 'integer', 'min:1', 'max:10'],
            'operational_score' => ['required', 'integer', 'min:1', 'max:10'],
            'regulatory_score' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'technical_score.required' => 'La calificación técnica es obligatoria.',
            'technical_score.integer' => 'La calificación técnica debe ser un número entero.',
            'technical_score.min' => 'La calificación técnica debe ser al menos 1.',
            'technical_score.max' => 'La calificación técnica no puede ser mayor a 10.',
            'financial_score.required' => 'La calificación financiera es obligatoria.',
            'financial_score.integer' => 'La calificación financiera debe ser un número entero.',
            'financial_score.min' => 'La calificación financiera debe ser al menos 1.',
            'financial_score.max' => 'La calificación financiera no puede ser mayor a 10.',
            'operational_score.required' => 'La calificación operativa es obligatoria.',
            'operational_score.integer' => 'La calificación operativa debe ser un número entero.',
            'operational_score.min' => 'La calificación operativa debe ser al menos 1.',
            'operational_score.max' => 'La calificación operativa no puede ser mayor a 10.',
            'regulatory_score.required' => 'La calificación normativa es obligatoria.',
            'regulatory_score.integer' => 'La calificación normativa debe ser un número entero.',
            'regulatory_score.min' => 'La calificación normativa debe ser al menos 1.',
            'regulatory_score.max' => 'La calificación normativa no puede ser mayor a 10.',
        ];
    }
}
