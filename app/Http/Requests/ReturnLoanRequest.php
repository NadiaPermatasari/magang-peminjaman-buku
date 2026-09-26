<?php

namespace App\Http\Requests;

use App\Enums\BookCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ReturnLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('returns.process');
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:60'],
            'condition' => ['required', new Enum(BookCondition::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
