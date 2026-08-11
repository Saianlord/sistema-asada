<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectApprovalRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'approval_agreement' => ['required', 'string', 'max:255'],
            'approval_date' => ['required', 'date'],
            'approval_responsible' => ['required', 'string', 'max:255'],
            'approval_justification' => ['required', 'string', 'max:2000'],
        ];
    }
}
