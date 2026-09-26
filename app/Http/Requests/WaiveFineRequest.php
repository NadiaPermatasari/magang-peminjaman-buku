<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WaiveFineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('waive', $this->route('fine'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
