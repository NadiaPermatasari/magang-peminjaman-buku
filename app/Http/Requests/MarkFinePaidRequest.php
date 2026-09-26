<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkFinePaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('markPaid', $this->route('fine'));
    }

    public function rules(): array
    {
        return [
            'method' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
