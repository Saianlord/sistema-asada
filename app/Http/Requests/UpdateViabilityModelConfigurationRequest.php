<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateViabilityModelConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'junta']);
    }

    public function rules(): array
    {
        return [
            'technical_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'financial_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'operational_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'regulatory_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'viable_threshold' => ['required', 'numeric', 'min:1', 'max:10'],
            'conditional_threshold' => ['required', 'numeric', 'min:1', 'max:10'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sum = (int) $this->technical_weight +
                   (int) $this->financial_weight +
                   (int) $this->operational_weight +
                   (int) $this->regulatory_weight;

            if ($sum !== 100) {
                $validator->errors()->add('technical_weight', 'La suma de las ponderaciones debe ser exactamente 100%.');
            }

            if ($this->filled('viable_threshold') && $this->filled('conditional_threshold')) {
                if ((float) $this->viable_threshold <= (float) $this->conditional_threshold) {
                    $validator->errors()->add('viable_threshold', 'El umbral viable debe ser estrictamente mayor que el umbral condicional.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'technical_weight.required' => 'La ponderación técnica es obligatoria.',
            'technical_weight.integer' => 'La ponderación técnica debe ser un número entero.',
            'technical_weight.min' => 'La ponderación técnica debe ser al menos 0%.',
            'technical_weight.max' => 'La ponderación técnica no puede ser mayor a 100%.',
            'financial_weight.required' => 'La ponderación financiera es obligatoria.',
            'financial_weight.integer' => 'La ponderación financiera debe ser un número entero.',
            'financial_weight.min' => 'La ponderación financiera debe ser al menos 0%.',
            'financial_weight.max' => 'La ponderación financiera no puede ser mayor a 100%.',
            'operational_weight.required' => 'La ponderación operativa es obligatoria.',
            'operational_weight.integer' => 'La ponderación operativa debe ser un número entero.',
            'operational_weight.min' => 'La ponderación operativa debe ser al menos 0%.',
            'operational_weight.max' => 'La ponderación operativa no puede ser mayor a 100%.',
            'regulatory_weight.required' => 'La ponderación normativa es obligatoria.',
            'regulatory_weight.integer' => 'La ponderación normativa debe ser un número entero.',
            'regulatory_weight.min' => 'La ponderación normativa debe ser al menos 0%.',
            'regulatory_weight.max' => 'La ponderación normativa no puede ser mayor a 100%.',
            'viable_threshold.required' => 'El umbral viable es obligatorio.',
            'viable_threshold.numeric' => 'El umbral viable debe ser un valor numérico.',
            'viable_threshold.min' => 'El umbral viable debe ser al menos 1.',
            'viable_threshold.max' => 'El umbral viable no puede ser mayor a 10.',
            'conditional_threshold.required' => 'El umbral condicional es obligatorio.',
            'conditional_threshold.numeric' => 'El umbral condicional debe ser un valor numérico.',
            'conditional_threshold.min' => 'El umbral condicional debe ser al menos 1.',
            'conditional_threshold.max' => 'El umbral condicional no puede ser mayor a 10.',
        ];
    }
}
