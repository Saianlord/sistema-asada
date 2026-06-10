<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $hasEvidence = $project && $project->evidence_path;

        return [
            'technical_justification' => ['required', 'string'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'impact' => ['required', 'string'],
            'risk' => ['required', 'string'],
            'evidence' => [$hasEvidence ? 'nullable' : 'required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg'],
        ];
    }

    public function messages(): array
    {
        return [
            'technical_justification.required' => 'La justificación técnica es obligatoria.',
            'technical_justification.string' => 'La justificación técnica debe ser una cadena de caracteres.',
            'estimated_cost.required' => 'El costo estimado es obligatorio.',
            'estimated_cost.numeric' => 'El costo estimado debe ser un valor numérico.',
            'estimated_cost.min' => 'El costo estimado no puede ser negativo.',
            'impact.required' => 'El impacto es obligatorio.',
            'impact.string' => 'El impacto debe ser una cadena de caracteres.',
            'risk.required' => 'El riesgo es obligatorio.',
            'risk.string' => 'El riesgo debe ser una cadena de caracteres.',
            'evidence.required' => 'El archivo de evidencia es obligatorio.',
            'evidence.file' => 'El archivo de evidencia debe ser un archivo válido.',
            'evidence.max' => 'El archivo de evidencia no debe pesar más de 10 MB.',
            'evidence.mimes' => 'El formato del archivo de evidencia debe ser pdf, png, jpg o jpeg.',
        ];
    }
}
