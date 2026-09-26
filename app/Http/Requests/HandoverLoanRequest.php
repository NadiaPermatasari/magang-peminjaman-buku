<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandoverLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('handover', $this->route('loan'));
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:60'],
        ];
    }
}
