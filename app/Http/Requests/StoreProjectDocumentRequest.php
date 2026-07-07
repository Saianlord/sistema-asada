<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'El documento es obligatorio.',
            'document.file' => 'Debe subir un archivo válido.',
            'document.mimes' => 'El formato del archivo no está permitido. Solo se aceptan: pdf, jpg, jpeg, png, doc, docx, xls, xlsx.',
            'document.max' => 'El tamaño del archivo no debe superar los 10 MB.',
        ];
    }
}
